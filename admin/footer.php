        <footer>
            <div class="container footer-container">
                <p>
                    <a href="../" target="_blank">Go to Front End</a> &middot; 
                    Powered by <a href="https://librekb.com/" target="_blank">LibreKB</a> 
                    <?php
                        $setting = new Setting();
                        $value = $setting->getSettingValue('version');
                        echo $value;
                    ?>
                </p>
            </div>
        </footer>
        <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>
</html>