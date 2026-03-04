<?php
// admin/includes/footer_admin.php
?>
            </div>
        </section>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Versión</b> 1.0.0
        </div>
        <strong>&copy; 2025 - <?php echo date('Y'); ?><a href="/"> NexusBuy</a>.</strong> Todos los derechos reservados.
    </footer>
</div>

<!-- jQuery -->
<script src="../../Util/js/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../../Util/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="../../Util/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="../../Util/js/datatables.min.js"></script>
<!-- Select2 -->
<script src="../../Util/js/select2.min.js"></script>
<!-- SweetAlert2 -->
<script src="../../Util/js/sweetalert2.min.js"></script>
<!-- Toastr -->
<script src="../../Util/js/toastr.min.js"></script>
<!-- Funciones generales -->
<script src="../../Util/js/funcion_general.js"></script>

<script>
    // Inicializar tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        $('.select2').select2();
    });
</script>
</body>
</html>