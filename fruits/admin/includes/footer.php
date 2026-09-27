<!-- Close #content and #wrapper -->
            </div>
        </div>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#sidebarCollapse').on('click', function() {
                    $('#sidebar').toggleClass('active');
                });
                
                // Delete confirmation
                $('.delete-btn').on('click', function(e) {
                    if(!confirm('Are you sure you want to delete this?')) {
                        e.preventDefault();
                    }
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <?php if(isset($_SESSION['success'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '<?php echo $_SESSION['success']; ?>',
                    timer: 3000
                });
            </script>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '<?php echo $_SESSION['error']; ?>',
                    timer: 3000
                });
            </script>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </body>
    </html>