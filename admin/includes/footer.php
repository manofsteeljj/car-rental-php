            <!-- Admin content ends -->
            </div>
    </div>
    
    <script src="/assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Admin sidebar toggle for mobile
            const menuToggle = document.querySelector('.menu-toggle');
            const adminSidebar = document.querySelector('.admin-sidebar');
            
            if (menuToggle && adminSidebar) {
                menuToggle.addEventListener('click', function() {
                    adminSidebar.classList.toggle('active');
                });
            }
            
            // Hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(alert => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.style.display = 'none';
                        }, 500);
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html>
