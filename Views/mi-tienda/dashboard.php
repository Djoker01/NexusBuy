<?php
$base_path = ""; // Ya está en la raíz
$pageTitle = "Panel de adminstración";
$pageName = "Dashboard";
$pageDescription = "Bienvenido de nuevo. Aquí está el resumen de tu tienda.";
include_once 'layouts/header.php';
?>
            
            

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="stat-value">$1,250 CUP</div>
                    <div class="stat-label">Ventas hoy</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +15% vs ayer
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                    <div class="stat-value">12</div>
                    <div class="stat-label">Pedidos nuevos</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +3 ayer
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon yellow">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="stat-value">345</div>
                    <div class="stat-label">Visitas hoy</div>
                    <div class="stat-trend">
                        <i class="fas fa-arrow-up"></i> +22% ayer
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon purple">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="stat-value">4.8 ★</div>
                    <div class="stat-label">Valoración promedio</div>
                    <div class="stat-trend" style="color: #6c757d;">
                        124 reseñas
                    </div>
                </div>
            </div>

            <!-- Charts and Alerts -->
            <div class="row">
                <!-- Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2>Ventas últimos 7 días</h2>
                        <a href="#">Ver detalle <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="chart-placeholder">
                        <div class="chart-bars">
                            <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                                <div style="display: flex; justify-content: space-around; width: 100%; height: 160px; align-items: flex-end;">
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 60px;"></div>
                                        <div class="bar-label">L</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 90px;"></div>
                                        <div class="bar-label">M</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 120px;"></div>
                                        <div class="bar-label">M</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 80px;"></div>
                                        <div class="bar-label">J</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 140px;"></div>
                                        <div class="bar-label">V</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 100px;"></div>
                                        <div class="bar-label">S</div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <div class="bar" style="height: 70px;"></div>
                                        <div class="bar-label">D</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="card">
                    <div class="card-header">
                        <h2>Alertas y notificaciones</h2>
                        <a href="#">Ver todas</a>
                    </div>
                    <div class="alert-list">
                        <div class="alert-item">
                            <div class="alert-icon warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>Stock bajo: Camiseta Negra (quedan 3 unidades)</div>
                        </div>
                        <div class="alert-item">
                            <div class="alert-icon info">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div>Pedido #2345 necesita confirmación</div>
                        </div>
                        <div class="alert-item">
                            <div class="alert-icon info">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div>3 mensajes sin leer en el chat</div>
                        </div>
                        <div class="alert-item">
                            <div class="alert-icon success">
                                <i class="fas fa-star"></i>
                            </div>
                            <div>Nueva reseña: "Excelente producto" ★★★★★</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h2>Pedidos recientes</h2>
                    <a href="#">Ver todos</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#2345</td>
                            <td>Juan Pérez</td>
                            <td>15/03 10:30</td>
                            <td>$450 CUP</td>
                            <td><span class="status pending"><i class="fas fa-clock"></i> Pendiente</span></td>
                            <td><button class="btn-icon"><i class="fas fa-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td>#2344</td>
                            <td>María Gómez</td>
                            <td>15/03 09:15</td>
                            <td>$890 CUP</td>
                            <td><span class="status confirmed"><i class="fas fa-check-circle"></i> Confirmado</span></td>
                            <td><button class="btn-icon"><i class="fas fa-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td>#2343</td>
                            <td>Carlos Ruiz</td>
                            <td>14/03 18:40</td>
                            <td>$1,200 CUP</td>
                            <td><span class="status shipped"><i class="fas fa-truck"></i> Enviado</span></td>
                            <td><button class="btn-icon"><i class="fas fa-eye"></i></button></td>
                        </tr>
                        <tr>
                            <td>#2342</td>
                            <td>Ana López</td>
                            <td>14/03 15:20</td>
                            <td>$340 CUP</td>
                            <td><span class="status confirmed"><i class="fas fa-check-circle"></i> Confirmado</span></td>
                            <td><button class="btn-icon"><i class="fas fa-eye"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Products and Calendar -->
            <div class="row">
                <!-- Top Products -->
                <div class="card">
                    <div class="card-header">
                        <h2>Productos más vendidos</h2>
                        <a href="#">Ver todos</a>
                    </div>
                    <div class="products-grid">
                        <div class="product-item">
                            <div class="product-image">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Camiseta Oversize Negra</div>
                                <div class="product-meta">45 vendidos</div>
                            </div>
                            <div class="product-price">$5,850</div>
                        </div>
                        <div class="product-item">
                            <div class="product-image">
                                <i class="fas fa-shoe-prints"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Zapatillas Running</div>
                                <div class="product-meta">21 vendidos</div>
                            </div>
                            <div class="product-price">$8,190</div>
                        </div>
                        <div class="product-item">
                            <div class="product-image">
                                <i class="fas fa-tshirt"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Jeans Skinny Azul</div>
                                <div class="product-meta">32 vendidos</div>
                            </div>
                            <div class="product-price">$7,360</div>
                        </div>
                        <div class="product-item">
                            <div class="product-image">
                                <i class="fas fa-hat-cowboy"></i>
                            </div>
                            <div class="product-info">
                                <div class="product-name">Gorra New Era</div>
                                <div class="product-meta">28 vendidos</div>
                            </div>
                            <div class="product-price">$4,200</div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="card">
                    <div class="card-header">
                        <h2>Próximas actividades</h2>
                        <a href="#">Ver calendario</a>
                    </div>
                    <div class="calendar-item">
                        <div class="calendar-date">Hoy</div>
                        <div class="calendar-event">3 pedidos por enviar</div>
                    </div>
                    <div class="calendar-item">
                        <div class="calendar-date">Mañana</div>
                        <div class="calendar-event">Reposición de inventario programada</div>
                    </div>
                    <div class="calendar-item">
                        <div class="calendar-date">18 Mar</div>
                        <div class="calendar-event">Inicio promoción "Fin de semana"</div>
                    </div>
                    <div class="calendar-item">
                        <div class="calendar-date">20 Mar</div>
                        <div class="calendar-event">Cierre de mes - Reportes</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (flotante para móvil) -->
            <div style="position: fixed; bottom: 2rem; right: 2rem; display: none;" class="mobile-quick-actions">
                <button style="width: 56px; height: 56px; border-radius: 50%; background: #4361ee; color: white; border: none; box-shadow: 0 4px 12px rgba(67,97,238,0.3); cursor: pointer;">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </main>
    </div>
<?php
include_once 'layouts/footer.php';
?>

    <script>
        // Simulación de interacciones (opcional)
        document.querySelectorAll('.btn-icon').forEach(btn => {
            btn.addEventListener('click', () => {
                alert('Vista detalle del pedido (demo)');
            });
        });

        
    </script>