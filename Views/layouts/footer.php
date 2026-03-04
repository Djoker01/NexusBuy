  </div>
  <!-- /.content-wrapper -->

  <!-- Modern Footer -->
  <footer class="main-footer" style="background: var(--gradient-primary); color: white; padding: 40px 0 20px; margin-top: 40px;">
    <div class="container-fluid">
      <div class="row">
        <!-- Company Info -->
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="footer-brand mb-3">
            <img src="<?php echo $base_path; ?>Util/img/png/logo.png" alt="NexusBuy" width="40" class="mr-2">
            <span class="h4 font-weight-bold">NexusBuy</span>
          </div>
          <p class="mb-3" style="opacity: 0.9;">
            Tu tienda online de confianza. Ofrecemos los mejores productos al mejor precio con envío rápido y seguro.
          </p>
          <div class="social-links" id="redes">
            <!-- Aqui se cargan las Redes Sociales -->
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-md-6 mb-4">
          <h5 class="font-weight-bold mb-3">Enlaces Rápidos</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="<?php echo $base_path; ?>index.php" class="text-white-50" style="text-decoration: none;">Inicio</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>producto.php" class="text-white-50" data-section="Productos" style="text-decoration: none;">Productos</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>ofertas.php" class="text-white-50" data-section="Ofertas" style="text-decoration: none;">Ofertas</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>producto.php?filtro=nuevos" class="text-white-50" data-section="Nuevos Productos" style="text-decoration: none;">Nuevos</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=contacto" class="text-white-50" data-section="Contacto" style="text-decoration: none;">Contacto</a></li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div class="col-lg-3 col-md-6 mb-4">
          <h5 class="font-weight-bold mb-3">Servicio al Cliente</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=centro_ayuda" class="text-white-50" data-section="Centro de Ayuda" style="text-decoration: none;">Centro de Ayuda</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=metodos_pago" class="text-white-50" data-section="Métodos de Pago" style="text-decoration: none;">Métodos de Pago</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=envios_entregas" class="text-white-50" data-section="Envíos y Entregas" style="text-decoration: none;">Envíos y Entregas</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=devoluciones" class="text-white-50" data-section="Devoluciones" style="text-decoration: none;">Devoluciones</a></li>
            <li class="mb-2"><a href="<?php echo $base_path_url; ?>soporte.php?filtro=terminos_condiciones" class="text-white-50" data-section="Términos y Condiciones" style="text-decoration: none;">Términos y Condiciones</a></li>
          </ul>
        </div>

        <!-- Contact & Newsletter -->
        <div class="col-lg-3 col-md-6 mb-4">
          <h5 class="font-weight-bold mb-3">Contacto</h5>
          <ul class="list-unstyled" id="datos_contacto">
            <!-- Aqui se cargan los Datos de Contacto -->
          </ul>
        </div>
      </div>

      <hr style="border-color: rgba(255,255,255,0.1);">

      <!-- Bottom Footer -->
      <div class="row align-items-center">
        <div class="col-md-6">
          <strong>Copyright &copy; 2014-2025 <a href="<?php echo $base_path; ?>index.php" class="text-white">NexusBuy</a>.</strong> 
          <span class="d-sm-inline-block">Todos los derechos reservados.</span>
        </div>
        <div class="col-md-6 text-md-right">
          <div class="mt-2 mt-md-0">
            <img src="<?php echo $base_path; ?>Util/Img/svg/es.svg" alt="Español" width="20" class="mr-1">
            <span>Español</span>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery --> 
<script src="<?php echo $base_path; ?>Util/Js/PDF Document creator.min.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/html2canvas.min.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/jquery.validate.min.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/bs-custom-file-input.min.js"></script>

<!-- Bootstrap 4 -->
<script src="<?php echo $base_path; ?>Util/Js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo $base_path; ?>Util/Js/adminlte.min.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/funcion_general.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/bootstrap.min.js"></script>
<script src="<?php echo $base_path; ?>Util/Js/sweetalert2.min.js"></script>
>

<!-- Script para funcionalidades del footer -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll para enlaces del footer
    document.querySelectorAll('footer a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
          behavior: 'smooth'
        });
      });
    });

    // Newsletter subscription
    const newsletterForm = document.querySelector('.newsletter');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        if (email) {
          // Simular suscripción
          Swal.fire({
            icon: 'success',
            title: '¡Gracias por suscribirte!',
            text: 'Te hemos enviado un email de confirmación.',
            timer: 3000,
            showConfirmButton: false
          });
          this.querySelector('input[type="email"]').value = '';
        }
      });
    }

    // ============================
    // FUNCIÓN "EN DESARROLLO"
    // ============================
    
    // Seleccionar todos los enlaces que deben mostrar el mensaje "en desarrollo"
    const enlacesEnDesarrollo = document.querySelectorAll('.en-desarrollo');
    
    // Función para mostrar el mensaje de "en desarrollo"
    function mostrarMensajeEnDesarrollo(seccion) {
      Swal.fire({
        title: '¡Próximamente!',
        html: `
          <div class="text-center">
            <i class="fas fa-tools fa-3x text-primary mb-3"></i>
            <h4 class="text-primary">${seccion}</h4>
            <p class="text-muted">Esta sección está en desarrollo y estará disponible muy pronto.</p>
            <div class="progress mt-3" style="height: 6px;">
              <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 75%"></div>
            </div>
            <small class="text-muted mt-2 d-block">75% completado</small>
          </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#4361ee',
        showCancelButton: false,
        customClass: {
          popup: 'en-desarrollo-popup'
        }
      });
    }

    // Agregar evento click a todos los enlaces "en desarrollo"
    enlacesEnDesarrollo.forEach(enlace => {
      enlace.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Obtener el nombre de la sección del atributo data-section
        const seccion = this.getAttribute('data-section') || 'Esta función';
        
        // Mostrar animación de carga breve antes del mensaje
        this.classList.add('loading');
        
        setTimeout(() => {
          this.classList.remove('loading');
          mostrarMensajeEnDesarrollo(seccion);
        }, 300);
      });
    });

    // Efecto hover mejorado para enlaces del footer
    const footerLinks = document.querySelectorAll('.footer-link');
    footerLinks.forEach(link => {
      link.addEventListener('mouseenter', function() {
        this.style.transform = 'translateX(5px)';
        this.style.transition = 'transform 0.3s ease';
      });
      
      link.addEventListener('mouseleave', function() {
        this.style.transform = 'translateX(0)';
      });
    });

    // Efecto especial para enlaces "en desarrollo"
    const enDesarrolloLinks = document.querySelectorAll('.en-desarrollo');
    enDesarrolloLinks.forEach(link => {
      // Agregar tooltip indicando que está en desarrollo
      link.setAttribute('title', 'En desarrollo - Próximamente');
      
      // Efecto de parpadeo sutil
      setInterval(() => {
        link.style.opacity = link.style.opacity === '0.7' ? '1' : '0.7';
      }, 2000);
    });
  });

  // Estilos adicionales para el mensaje "en desarrollo"
  const style = document.createElement('style');
  style.textContent = `
    .en-desarrollo-popup {
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    
    .en-desarrollo-popup .swal2-title {
      color: #4361ee;
      font-weight: 600;
    }
    
    .footer-link.loading {
      position: relative;
      overflow: hidden;
    }
    
    .footer-link.loading::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      animation: loading 1s infinite;
    }
    
    @keyframes loading {
      0% { left: -100%; }
      100% { left: 100%; }
    }
    
    .en-desarrollo {
      position: relative;
      cursor: pointer;
    }
    
    .en-desarrollo::before {
      content: '🔨';
      margin-right: 5px;
      font-size: 0.8em;
    }
  `;
  document.head.appendChild(style);
</script>

</body>
</html>