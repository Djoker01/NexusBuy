<?php
include_once 'Views/Layauts/header.php';
?>
<title>Home | NexusBuy</title>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Home</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="index.php">Home</a></li>
          <li class="breadcrumb-item active">Home</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<style>
.titulo_producto{
  color: #000;
}
.titulo_producto:visited{
  color: #000;
}
.titulo_producto:focus{
  border-bottom: 1px solid;
}
.titulo_producto:hover{
  border-bottom: 1px solid;
}
.titulo_producto:active{
  background: #000;
  color: #fff;
}
</style>

<section class="content">

  <!-- Default box -->
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Productos</h3>
    </div>
    <div class="card-body">
      <div id="productos" class="row">
        <div class="col-sm-2">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-sm-12">
                  <img src="Util/Img/Producto/producto_default.png" class="img-fluid">
                </div>
                <div class="col-sm-12">
                  <span class="text-muted float-left">Marca</span></br>
                  <a class="titulo_producto" href="#">Titulo del producto</a></br>
                  <span class="badge bg-success">Envio gratis</span></br>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="fas fa-star text-warning"></i>
                  <i class="far fa-star text-warning"></i>
                  <i class="far fa-star text-warning"></i></br>
                  <span class="text-muted" style="text-decoration: line-through">$ 1000</span>
                  <span class="text-muted">-10%</span></br>
                  <h4 class="text-danger">$ 900</h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.card-body -->
    <!-- <div class="card-footer">
      Footer
    </div> -->
    <!-- /.card-footer-->
  </div>
  <!-- /.card -->

</section>
<?php
include_once 'Views/Layauts/footer.php';
?>
<script src="index.js"></script>
