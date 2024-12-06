<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    
    require_once('../config.php');
    if (isset($_GET['action']) && $_GET['action'] === 'updateCheck') {
        /* Update Check */
        $config = new Config();
        $isUpdate = $config->updateCheck;
        if ($isUpdate == 'yes') {
            $setting = new Setting();
            $currentVersion = $setting->getSettingValue('version');
            $latestJson = file_get_contents('https://librekb.com/latest.php?version=' . $currentVersion);
            $latestData = json_decode($latestJson, true);
            if ($latestData && isset($latestData['version'])) {
                $latestVersion = $latestData['version'];
                if ($currentVersion != $latestVersion) {
                    header('Location: index.php?msg=update');
                    exit;
                } else {
                    header('Location: index.php');
                    exit;
                }
            }
        } else {
            header('Location: index.php');
            exit;
        }
        
    } else if (isset($_GET['action']) && $_GET['action'] === 'categoryManage') {
        /* Category - Manage Category */ 
        if (isset($_GET['c'])) {
            $get = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');
            $category = new Category();
            $categoryData = $category->getCategory($get);
            if (!$categoryData) {
                header('Location: index.php?msg=error');
                exit;
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $parent = $_POST['parent'];
                    $name = $_POST['name'];
                    $slug = $_POST['slug'];
                    $description = $_POST['description'];
                    $icon = $_POST['icon'];
                    $order = $_POST['order'];
                    $status = $_POST['status'];
        
                    $category = new Category();
                    $category->id = $get;
                    $category->parent = $parent;
                    $category->name = $name;
                    $category->slug = $slug;
                    $category->description = $description;
                    $category->icon = $icon;
                    $category->order = $order;
                    $category->status = $status;
                    $categoryUpdated = $category->updateCategory();
        
                    if ($categoryUpdated) {
                        header('Location: index.php?msg=success');
                        exit;
                    } else {
                        header('Location: index.php?msg=error');
                        exit;
                    }
                } else {
                    header('Location: index.php?msg=error');
                    exit;
                }
            }
        } else {
            header('Location: index.php?msg=error');
            exit;
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'categoryDelete') {
        /* Category - Delete Category */ 
        if (isset($_GET['c'])) {
            $get = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');
            $category = new Category();
            $categoryData = $category->getCategory($get);
            if (!$categoryData) {
                header('Location: index.php?msg=error');
                exit;
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $category = new Category();
                    $category->id = $get;
                    $categoryDeleted = $category->deleteCategory();
                    if ($categoryDeleted) {
                        header('Location: index.php?msg=success');
                        exit;
                    } else {
                        header('Location: index.php?msg=error');
                        exit;
                    }
                } else {
                    header('Location: index.php?msg=error');
                    exit;
                }
            }
        } else {
            header('Location: index.php?msg=error');
            exit;
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'categoryView') {
        /* Category - View Category */ 
        if (isset($_GET['c'])) {
            $get = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');
            $category = new Category();
            $categoryData = $category->getCategory($get);
            if (!$categoryData) {
                header('Location: index.php?msg=error');
                exit;
            } else {
                $pageCategory = "Dashboard";
                $pageTitle = $categoryData['name'];
                require_once('header.php');
            ?>
                <div class="container">
                    <header>
                        <h1><?php echo $pageTitle; ?></h1>
                    </header>
                    <br />
                    <main>
                        <?php
                            $article = new Article();
                            $articles = $article->getArticlesByCategoryId($categoryData['id']);
                            if (!$articles) {
                                echo '<p><i>No articles in this category.</i></p>';
                            } else {
                                foreach($articles as $article) {
                                    echo '
                                        <div class="article-item">
                                            <a href="index.php?action=articleManage&a=' . $article['id'] . '">
                                                <div class="article-content">
                                                    <h6><i class="bi bi-file-earmark"></i>  ' . $article['title'] . '</h6>
                                                    <p>Order: <code>' . $article['order'] . '</code> Status: <code>' . $article['status'] . '</code> Created: <code>' . $article['created'] . '</code> Updated: <code>' . $article['updated'] . '</code></p>
                                                </div>
                                            </a>
                                        </div>
                                        ';
                                }
                            }
                        ?>
                        <a href="index.php?action=articleCreate&c=<?php echo $categoryData['id']; ?>" class="btn btn-dark">Create Article</a>
                    </main>
                </div>
            <?php 
                require_once('footer.php');
            }
        } else {
            header('Location: index.php');
            exit;
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'categoryCreate') {
        /* Category - Create Category */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $parent = $_POST['parent'];
            $name = $_POST['name'];
            $slug = $_POST['slug'];
            $description = $_POST['description'];
            $icon = $_POST['icon'];
            $order = $_POST['order'];
            $status = $_POST['status'];
            $category = new Category();
            $category->parent = $parent;
            $category->name = $name;
            $category->slug = $slug;
            $category->description = $description;
            $category->icon = $icon;
            $category->order = $order;
            $category->status = $status;
            $categoryCreated = $category->createCategory();
            if ($categoryCreated) {
                header('Location: index.php?msg=success');
                exit;
            } else {
                header('Location: index.php?msg=error');
                exit;
            }
        } else {
            $pageCategory = "Dashboard";
            $pageTitle = "Create Category";
            require_once('header.php');
            ?>
                <div class="container">
                    <header>
                        <h1><?php echo $pageTitle; ?></h1>
                    </header>
                    <main>
                        <form action="index.php?action=categoryCreate" method="POST">
                            <div class="mb-3">
                                <label for="parent" class="form-label">Parent Category</label>
                                <select class="form-select" id="parent" name="parent">
                                    <option value="0">None, this will be a top level category</option>
                                    <?php
                                        $category = new Category();
                                        $categories = $category->getAllCategoriesTopLevel();
                                        foreach ($categories as $cat) {
                                            echo '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="order" class="form-label">Order</label>
                                <input type="number" class="form-control" id="order" name="order" value="0">
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="enabled">Enabled</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark">Submit</button>
                        </form>
                    </main>
                </div>
            <?php 
            require_once('footer.php');
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'articleDelete') {
        /* Article - Delete Article */
        if (isset($_GET['a'])) {
            $get = htmlspecialchars($_GET['a'], ENT_QUOTES, 'UTF-8');
            $article = new Article();
            $articleData = $article->getArticle($get);
            if (!$articleData) {
                header('Location: index.php?msg=error');
                exit;
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $article = new Article();
                    $article->id = $get;
                    $articleDeleted = $article->deleteArticle();
                    if ($articleDeleted) {
                        header('Location: index.php?msg=success');
                        exit;
                    } else {
                        header('Location: index.php?msg=error');
                        exit;
                    }
                } else {
                    header('Location: index.php?msg=error');
                    exit;
                }
            }
        } else {
            header('Location: index.php?msg=error');
            exit;
        }
    } else if (isset($_GET['action']) && $_GET['action'] === 'articleManage') {
        /* Article - Manage Article */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $content = $_POST['content'];
            $status = $_POST['status'];
            $order = $_POST['order'];
            $category = $_POST['category'];
            $article_id = $_POST['article_id'];
            $article = new Article();
            $article->title = $title;
            $article->content = $content;
            $article->status = $status;
            $article->order = $order;
            $article->category = $category;
            $article->id = $article_id;
            $articleUpdated = $article->updateArticle();
            if ($articleUpdated) {
                header('Location: index.php?action=articleManage&a='.$article_id.'&msg=success');
                exit;
            } else {
                header('Location: index.php?action=categoryView&c='.$article_id.'&msg=error');
                exit;
            }
        } else {
            if (isset($_GET['a'])) {
                $get = htmlspecialchars($_GET['a'], ENT_QUOTES, 'UTF-8');
                $article = new Article();
                $articleData = $article->getArticle($get);
                if (!$articleData) {
                    header('Location: index.php?msg=error');
                    exit;
                } else {
                    $pageCategory = "Dashboard";
                    $pageTitle = $articleData['title'];
    
                    require_once('header.php');
                ?>
    
                    <div class="container">
                        <?php
                            if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
                                echo '<div class="alert alert-danger" role="alert">There was an error processing your request.</div>';
                            }
                            if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
                                echo '<div class="alert alert-success" role="alert">Article updated</div>';
                            }
                        ?>
                        <header>
                            <h1><?php echo $pageTitle; ?></h1>
                        </header>
                        <main>
                            <form action="index.php?action=articleManage&a=<?php echo $articleData['id']; ?>" method="POST">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo $articleData['title']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea class="form-control tinymce" id="content" name="content" rows="3"><?php echo $articleData['content']; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="enabled" <?php if ($articleData['status'] == 'enabled') { echo "selected"; } ?>>Enabled</option>
                                            <option value="disabled" <?php if ($articleData['status'] == 'disabled') { echo "selected"; } ?>>Disabled</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                        <?php
                                            $category = new Category();
                                            $categories = $category->getAllCategories();
                                            foreach($categories as $category) {
                                                if ($category['id'] == $articleData['category']) {
                                                    echo '<option value="' . $category['id'] . '" selected>' . $category['name'] . '</option>';
                                                } else {
                                                    echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                                                }
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="order" class="form-label">Order</label>
                                        <input type="number" class="form-control" id="order" name="order" required value="<?php echo $articleData['order']; ?>">
                                    </div>
                                    <input type="text" class="form-control" id="article_id" name="article_id" value="<?php echo $articleData['id']; ?>" hidden>
                                    <button type="submit" class="btn btn-dark">Submit</button>
                                </form>
                                <div class="article-delete">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#deleteArticle">Delete Article</a>
                                </div>
                                <!-- Delete Article Modal -->
                                <div class="modal fade" id="deleteArticle" tabindex="-1" aria-labelledby="deleteArticleLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="deleteArticleLabel">Delete Article</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this article?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <form action="index.php?action=articleDelete&a=<?php echo $articleData['id']; ?>" method="POST">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                        </main>
    
                <?php 
                    require_once('footer.php');
                }
            } else {
                header('Location: index.php');
                exit;
            }
        }
        
    } else if (isset($_GET['action']) && $_GET['action'] === 'articleCreate') {
        /* Article - Create Article */
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $slug = $_POST['slug'];
            $category = $_POST['category'];
            $content = $_POST['content'];
            $order = $_POST['order'];
            $status = $_POST['status'];
            $featured = $_POST['featured'];
            $article = new Article();
            $article->title = $title;
            $article->slug = $slug;
            $article->category = $category;
            $article->content = $content;
            $article->order = $order;
            $article->status = $status;
            $article->featured = $featured;
            $articleCreated = $article->createArticle();
            if ($articleCreated) {
                header('Location: index.php?action=categoryView&c='.$_POST['category'].'&msg=success');
                exit;
            } else {
                header('Location: index.php?action=categoryView&c='.$_POST['category'].'&msg=error');
                exit;
            }
        } else {
            if (isset($_GET['c'])) {
                $get = htmlspecialchars($_GET['c'], ENT_QUOTES, 'UTF-8');
                $category = new Category();
                $categoryData = $category->getCategory($get);
                if (!$categoryData) {
                    header('Location: index.php?msg=error');
                    exit;
                } else {
                    $pageCategory = "Dashboard";
                    $pageTitle = "Create Article in " . $categoryData['name'];
    
                    require_once('header.php');
                    ?>
    
                    <div class="container">
                        <header>
                            <h1><?php echo $pageTitle; ?></h1>
                        </header>
                        <main>
                            <form action="index.php?action=articleCreate" method="POST">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <textarea class="form-control tinymce" id="content" name="content" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="enabled">Enabled</option>
                                        <option value="disabled">Disabled</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                        <label for="order" class="form-label">Order</label>
                                        <input type="number" class="form-control" id="order" name="order" value="0" required>
                                    </div>
                                <input type="text" class="form-control" id="category" name="category" value="<?php echo $categoryData['id']; ?>" hidden>
                                <button type="submit" class="btn btn-dark">Submit</button>
                            </form>
                        </main>
    
                    <?php
    
                    require_once('footer.php');
                }
            } else {
                header('Location: index.php');
                exit;
            }
        }
        
    } else { 
        /* Dashboard */
        $pageCategory = "Dashboard";
        $pageTitle = "Categories";
        require_once('header.php');
        ?>
            <div class="container">
                <?php
                    if (isset($_GET['msg']) && $_GET['msg'] === 'error') {
                        echo '<div class="alert alert-danger" role="alert">There was an error processing your request.</div>';
                    }
                    if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
                        echo '<div class="alert alert-success" role="alert">Action performed succesfully.</div>';
                    }
                    if (isset($_GET['msg']) && $_GET['msg'] === 'update') {
                        echo '<div class="alert alert-primary" role="alert">An update to LibreKB is available. Consider updating for the latest features and security enhancements. Get the latest version at <a href="https://librekb.com/" target="_blank">LibreKB.com</a>. If you wish to disable update checks, set updateCheck to no in the config file.</div>';
                    }
                    
                ?>
                <header>
                    <h1><?php echo $pageTitle; ?></h1>
                </header>
                <br />
                <?php
                    $category = new Category();
                    $categories = $category->getAllCategoriesTopLevel();
                    if (!$categories) {
                        echo '<p><i>No categories present.</i></p>';
                    } else {
                        foreach($categories as $category) {
                            $article = new Article();
                            $numArticlesInCategory = $article->getNumberOfArticlesByCategoryId($category['id']);
                            $category2 = new Category();
                            $numCategoriesInCategory = $category2->getNumberOfCategoriesWithThisParent($category['id']);
                            echo '
                            <div class="category-item">
                                <a href="index.php?action=categoryView&c=' . $category['id'] . '">
                                    <div class="category-inner">
                                        <div class="category-icon">
                                            <i class="bi bi-' . $category['icon'] . '"></i>
                                        </div>
                                        <div class="category-content">
                                            <h6>' . $category['name'] . ' <span class="num-articles">(' . $numArticlesInCategory . ')</span></h6>
                                            <p>Order: <code>' . $category['order'] . '</code> Status: <code>' . $category['status'] . '</code> </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="category-manage">
                                ' . $category['name'] . ': <a href="index.php?action=articleCreate&c=' . $category['id'] . '">Create Article</a> &middot; <a href="#" data-bs-toggle="modal" data-bs-target="#editCategory' . $category['id'] . '">Edit</a>'.(($numArticlesInCategory=='0' && $numCategoriesInCategory=='0')?' &middot; <a href="#" data-bs-toggle="modal" data-bs-target="#deleteCategory' . $category['id'] . '">Delete</a>':"").'
                            </div>
                            <!-- Edit Category Modal -->
                            <div class="modal fade" id="editCategory' . $category['id'] . '" tabindex="-1" aria-labelledby="editCategory' . $category['id'] . 'Label" aria-hidden="true">
                                <form action="index.php?action=categoryManage&c=' . $category['id'] . '" method="POST">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="editCategory' . $category['id'] . 'Label">' . $category['name'] . '</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="parent" class="form-label">Parent Category</label>';
                                                    $categoryTL = new Category();
                                                    $categoriesTL = $categoryTL->getAllCategoriesTopLevel();
                                                    echo '<select class="form-select" id="parent" name="parent">
                                                        <option value="0">None, this will be a top level category</option>';
                                                        foreach ($categoriesTL as $cat) {
                                                            if ($cat['id'] != $category['id']) {
                                                                echo '<option value="' . $cat['id'] . '" '.(($category['parent']==$cat['id'])?'selected="selected"':"").'>' . $cat['name'] . '</option>';
                                                            }
                                                        }
                                                    echo '</select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="name" name="name" value="' . $category['name'] . '" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="description" class="form-label">Description</label>
                                                    <textarea class="form-control" id="description" name="description" rows="3">' . $category['description'] . '</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="order" class="form-label">Order</label>
                                                    <input type="number" class="form-control" id="order" name="order"  value="' . $category['order'] . '" >
                                                </div>
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select class="form-select" id="status" name="status">
                                                        <option value="enabled" '.(($category['status']=='enabled')?'selected="selected"':"").'>Enabled</option>
                                                        <option value="disabled" '.(($category['status']=='disabled')?'selected="selected"':"").'>Disabled</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- Delete Category Modal -->
                            <div class="modal fade" id="deleteCategory' . $category['id'] . '" tabindex="-1" aria-labelledby="deleteCategory' . $category['id'] . 'Label" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="deleteCategory' . $category['id'] . 'Label">Delete ' . $category['name'] . '</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this category?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <form action="index.php?action=categoryDelete&c=' . $category['id'] . '" method="POST">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            ';
                            $categorySub = new Category();
                            $categoriesSub = $categorySub->getAllCategoriesWithParent($category['id']);
                            foreach($categoriesSub as $categorySub) {
                                $article = new Article();
                                $numArticlesInCategorySub = $article->getNumberOfArticlesByCategoryId($categorySub['id']);
                                $category2 = new Category();
                                $numCategoriesInCategorySub = $category2->getNumberOfCategoriesWithThisParent($categorySub['id']);
                                echo '
                                <div class="category-item subcategory">
                                    <a href="index.php?action=categoryView&c=' . $categorySub['id'] . '">
                                        <div class="category-inner">
                                            <div class="category-icon">
                                                <i class="bi bi-' . $categorySub['icon'] . '"></i>
                                            </div>
                                            <div class="category-content">
                                                <h6>' . $categorySub['name'] . ' <span class="num-articles">(' . $numArticlesInCategorySub . ')</span></h6>
                                                <p>Order: <code>' . $categorySub['order'] . '</code> Status: <code>' . $categorySub['status'] . '</code> </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="category-manage subcategory">
                                    ' . $categorySub['name'] . ': <a href="index.php?action=articleCreate&c=' . $categorySub['id'] . '">Create Article</a> &middot; <a href="#" data-bs-toggle="modal" data-bs-target="#editCategory' . $categorySub['id'] . '">Edit</a>'.(($numArticlesInCategorySub=='0' && $numCategoriesInCategorySub=='0')?' &middot; <a href="#" data-bs-toggle="modal" data-bs-target="#deleteCategory' . $categorySub['id'] . '">Delete</a>':"").'
                                </div>
                                <!-- Edit Category Modal -->
                                <div class="modal fade" id="editCategory' . $categorySub['id'] . '" tabindex="-1" aria-labelledby="editCategory' . $categorySub['id'] . 'Label" aria-hidden="true">
                                    <form action="index.php?action=categoryManage&c=' . $categorySub['id'] . '" method="POST">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="editCategory' . $categorySub['id'] . 'Label">' . $categorySub['name'] . '</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="parent" class="form-label">Parent Category</label>';
                                                        $categoryTL = new Category();
                                                        $categoriesTL = $categoryTL->getAllCategoriesTopLevel();
                                                        echo '<select class="form-select" id="parent" name="parent">
                                                            <option value="0">None, this will be a top level category</option>';
                                                            foreach ($categoriesTL as $cat) {
                                                                if ($cat['id'] != $categorySub['id']) {
                                                                    echo '<option value="' . $cat['id'] . '" '.(($categorySub['parent']==$cat['id'])?'selected="selected"':"").'>' . $cat['name'] . '</option>';
                                                                }
                                                            }
                                                        echo '</select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="name" class="form-label">Name</label>
                                                        <input type="text" class="form-control" id="name" name="name" value="' . $categorySub['name'] . '" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="description" class="form-label">Description</label>
                                                        <textarea class="form-control" id="description" name="description" rows="3">' . $categorySub['description'] . '</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="order" class="form-label">Order</label>
                                                        <input type="number" class="form-control" id="order" name="order"  value="' . $categorySub['order'] . '" >
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">Status</label>
                                                        <select class="form-select" id="status" name="status">
                                                            <option value="enabled" '.(($categorySub['status']=='enabled')?'selected="selected"':"").'>Enabled</option>
                                                            <option value="disabled" '.(($categorySub['status']=='disabled')?'selected="selected"':"").'>Disabled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- Delete Category Modal -->
                                <div class="modal fade" id="deleteCategory' . $categorySub['id'] . '" tabindex="-1" aria-labelledby="deleteCategory' . $categorySub['id'] . 'Label" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="deleteCategory' . $categorySub['id'] . 'Label">Delete ' . $categorySub['name'] . '</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this category?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <form action="index.php?action=categoryDelete&c=' . $categorySub['id'] . '" method="POST">
                                                <button type="submit" class="btn btn-danger">Delete</button>
                                            </form>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                ';
                            }
                        }
                    }
                    
                ?>
                <a href="index.php?action=categoryCreate" class="btn btn-dark">Create Category</a>
            </div>
            
            
        <?php
        require_once('footer.php');
    }
?>