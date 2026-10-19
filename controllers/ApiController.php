<?php

/**
 * Read-only JSON API for articles and categories.
 *
 * Responses follow the same visibility rules as the public site: disabled
 * articles are hidden, and an enabled article inside a disabled category
 * (or a category with a disabled ancestor) is hidden.
 */
class ApiController extends Controller
{
    const MAX_PER_PAGE = 50;
    const DEFAULT_PER_PAGE = 20;
    const MAX_PAGE = 1000;
    const MAX_QUERY_LENGTH = 200;

    private $settingModel;
    private $categoryModel;
    private $articleModel;
    private $attachmentModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->categoryModel = new Category();
        $this->articleModel = new Article();
        $this->attachmentModel = new Attachment();
    }

    public function preflight()
    {
        $this->sendCors();
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: X-API-Key, Content-Type');
        header('Access-Control-Max-Age: 600');
        header('Allow: GET, OPTIONS');
        header('Content-Length: 0');
        http_response_code(204);
        exit;
    }

    public function reject($status, $message)
    {
        $this->fail((int)$status, $message);
    }

    public function searchArticles()
    {
        try {
            $this->guardRead();
            list($page, $perPage) = $this->pagination();
            $query = $this->searchQuery();

            $hiddenCategories = $this->categoryModel->getAllDisabledCategoryIds();
            $total = $this->articleModel->countPublicSearch($query, $hiddenCategories);
            $offset = ($page - 1) * $perPage;
            $rows = $this->articleModel->searchPublic($query, $hiddenCategories, $perPage, $offset);

            $articles = [];
            foreach ($rows as $row) {
                $summary = $this->articleSummary($row);
                $summary['attachments'] = $this->attachmentPayloads($row['id']);
                $articles[] = $summary;
            }

            $this->respond([
                'query' => $query,
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'articles' => $articles
            ]);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    public function articleById($id)
    {
        try {
            $this->guardRead();
            $articleId = $this->requireId($id);
            $article = $this->articleModel->find($articleId);
            $this->respondArticle($article);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    public function articleBySlug($slug)
    {
        try {
            $this->guardRead();
            $slug = $this->requireSlug($slug);
            $article = $this->articleModel->getEnabledBySlug($slug);
            $this->respondArticle($article);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    public function categories()
    {
        try {
            $this->guardRead();
            $this->respond([
                'categories' => $this->categoryNodes(null)
            ]);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    public function categoryById($id)
    {
        try {
            $this->guardRead();
            $categoryId = $this->requireId($id);
            $category = $this->categoryModel->find($categoryId);
            $this->respondCategory($category);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    public function categoryBySlug($slug)
    {
        try {
            $this->guardRead();
            $slug = $this->requireSlug($slug);
            $category = $this->categoryModel->getEnabledBySlug($slug);
            $this->respondCategory($category);
        } catch (Throwable $e) {
            $this->fail(500, 'The request could not be completed.');
        }
    }

    private function guardRead()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method !== 'GET') {
            $this->fail(405, 'Method not allowed.');
        }

        if ($this->settingModel->getValue('maintenance_mode') === 'enabled') {
            $this->fail(503, 'The knowledge base is undergoing maintenance.');
        }

        if ($this->settingModel->getValue('api_enabled') !== 'enabled') {
            $this->fail(503, 'The API is disabled.');
        }

        $apiKey = trim((string)$this->settingModel->getValue('api_key'));
        $visibility = $this->settingModel->getValue('kb_visibility');
        if ($visibility !== 'public' && $visibility !== 'private') {
            $visibility = 'public';
        }

        if ($apiKey !== '') {
            if (!$this->keyMatches($apiKey, $this->providedKey())) {
                $this->fail(401, 'A valid API key is required.');
            }
            return;
        }

        if ($visibility === 'private') {
            $this->fail(403, 'An API key is required when the knowledge base is private.');
        }
    }

    private function providedKey()
    {
        $header = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if (is_string($header) && trim($header) !== '') {
            return trim($header);
        }

        $query = $_GET['api_key'] ?? '';
        if (!is_string($query)) {
            return '';
        }

        return trim($query);
    }

    /**
     * Compare hashes so the check stays the same length regardless of the key.
     */
    private function keyMatches($stored, $provided)
    {
        if (!is_string($stored) || !is_string($provided) || $stored === '' || $provided === '') {
            return false;
        }

        return hash_equals(hash('sha256', $stored), hash('sha256', $provided));
    }

    private function respondArticle($article)
    {
        if (!$article || ($article['status'] ?? '') !== 'enabled') {
            $this->fail(404, 'Article not found.');
        }

        if (!$this->categoryModel->isCategoryHierarchyEnabled($article['category'])) {
            $this->fail(404, 'Article not found.');
        }

        $category = $this->categoryModel->find($article['category']);
        if (!$category) {
            $this->fail(404, 'Article not found.');
        }

        $this->respond([
            'article' => [
                'id' => (int)$article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'content' => HtmlSanitizer::clean($article['content'] ?? ''),
                'category' => [
                    'id' => (int)$category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug']
                ],
                'attachments' => $this->attachmentPayloads($article['id']),
                'created' => $article['created'],
                'updated' => $article['updated']
            ]
        ]);
    }

    private function articleSummary($article)
    {
        $category = $this->categoryModel->find($article['category']);

        return [
            'id' => (int)$article['id'],
            'title' => $article['title'],
            'slug' => $article['slug'],
            'category' => $category ? [
                'id' => (int)$category['id'],
                'name' => $category['name'],
                'slug' => $category['slug']
            ] : null
        ];
    }

    private function attachmentPayloads($articleId)
    {
        $rows = $this->attachmentModel->getByArticleId($articleId);
        $items = [];

        foreach ($rows as $row) {
            $items[] = [
                'id' => (int)$row['id'],
                'name' => $row['original_name'],
                'size' => (int)$row['size'],
                'mime' => $row['mime_type'],
                'url' => $this->absoluteUrl('/attachment/' . (int)$row['id'])
            ];
        }

        return $items;
    }

    private function respondCategory($category)
    {
        if (!$category || !$this->categoryModel->isCategoryHierarchyEnabled($category['id'])) {
            $this->fail(404, 'Category not found.');
        }

        $articles = [];
        foreach ($this->articleModel->getEnabledByCategoryId($category['id']) as $article) {
            $articles[] = [
                'id' => (int)$article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'attachments' => $this->attachmentPayloads($article['id'])
            ];
        }

        $node = $this->categoryNode($category);
        $node['children'] = $this->categoryNodes((int)$category['id']);
        $node['articles'] = $articles;

        $this->respond(['category' => $node]);
    }

    private function categoryNodes($parentId)
    {
        $rows = $parentId === null
            ? $this->categoryModel->getEnabledTopLevel()
            : $this->categoryModel->getEnabledWithParent($parentId);

        $nodes = [];
        foreach ($rows as $row) {
            $node = $this->categoryNode($row);
            $node['children'] = $this->categoryNodes((int)$row['id']);
            $nodes[] = $node;
        }

        return $nodes;
    }

    private function categoryNode($category)
    {
        $parent = $category['parent'];

        return [
            'id' => (int)$category['id'],
            'name' => $category['name'],
            'slug' => $category['slug'],
            'description' => $category['description'] ?? '',
            'icon' => $category['icon'] ?? '',
            'parent_id' => ($parent === null || $parent === '') ? null : (int)$parent,
            'article_count' => (int)$this->categoryModel->countEnabledArticlesRecursive($category['id'])
        ];
    }

    private function searchQuery()
    {
        $query = $_GET['q'] ?? '';
        if (!is_string($query)) {
            $this->fail(400, 'Invalid search query.');
        }

        $query = trim($query);
        if ($query === '') {
            $this->fail(400, 'A search query is required.');
        }

        if (strlen($query) > self::MAX_QUERY_LENGTH) {
            $this->fail(400, 'The search query is too long.');
        }

        return $query;
    }

    private function pagination()
    {
        $page = $this->numericQuery('page', '1', 1, self::MAX_PAGE, 'Invalid page.');
        $perPage = $this->numericQuery('per_page', (string)self::DEFAULT_PER_PAGE, 1, self::MAX_PER_PAGE, 'Invalid per_page.');
        return [$page, $perPage];
    }

    private function numericQuery($name, $default, $min, $max, $message)
    {
        if (!array_key_exists($name, $_GET) || $_GET[$name] === '') {
            return (int)$default;
        }

        $value = $_GET[$name];
        if (!is_string($value) || !ctype_digit($value)) {
            $this->fail(400, $message);
        }

        $number = (int)$value;
        if ($number < $min || $number > $max) {
            $this->fail(400, $message);
        }

        return $number;
    }

    private function requireId($id)
    {
        if (!is_string($id) || !ctype_digit($id) || $id === '0' || strlen($id) > 10) {
            $this->fail(400, 'Invalid id.');
        }

        $number = (int)$id;
        if ($number < 1 || $number > 2147483647) {
            $this->fail(400, 'Invalid id.');
        }

        return $number;
    }

    private function requireSlug($slug)
    {
        if (!is_string($slug) || $slug === '' || strlen($slug) > 255) {
            $this->fail(400, 'Invalid slug.');
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $slug)) {
            $this->fail(400, 'Invalid slug.');
        }

        return $slug;
    }

    private function absoluteUrl($path)
    {
        $config = new Config();
        $base = rtrim((string)$config->systemURL, '/');
        $path = '/' . ltrim($path, '/');

        if ($base === '') {
            return $this->url(ltrim($path, '/'));
        }

        return $base . $path;
    }

    private function corsOrigin()
    {
        $origin = trim((string)$this->settingModel->getValue('api_cors_origin'));
        if ($origin === '') {
            return '*';
        }
        if ($origin === '*') {
            return '*';
        }
        if (preg_match('#\Ahttps?://[A-Za-z0-9.-]+(?::[0-9]{1,5})?\z#', $origin)) {
            return $origin;
        }

        return '';
    }

    private function sendCors()
    {
        try {
            $origin = $this->corsOrigin();
        } catch (Throwable $e) {
            $origin = '*';
        }

        if ($origin === '') {
            return;
        }

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }

    private function respond($data, $status = 200)
    {
        $this->sendCors();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            http_response_code(500);
            $json = '{"error":"The request could not be completed."}';
        }

        echo $json;
        exit;
    }

    private function fail($status, $message)
    {
        if ($status === 405) {
            header('Allow: GET, OPTIONS');
        }

        $this->respond(['error' => $message], $status);
    }
}
