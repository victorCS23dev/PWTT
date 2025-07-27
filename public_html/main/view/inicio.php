<?php
// view/dashboard_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<div class="container-fluid mt-4">
    <h1 class="h2 mb-4">📊 Dashboard de Ventas</h1>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Productos Más Vendidos (Top 10)</h5>
                </div>
                <div class="card-body">
                    <div class="text-center" id="loading-most-sold-products">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando productos más vendidos...</span>
                        </div>
                        <p class="mt-2">Cargando datos...</p>
                    </div>
                    <canvas id="mostSoldProductsChart" style="max-height: 400px;"></canvas>
                    <div id="most-sold-products-message" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Otras Métricas Clave</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">Ventas Totales del Mes: <span class="fw-bold text-success" id="metric-total-sales">S/ 0.00</span></li>
                        <li class="list-group-item">Pedidos Pendientes: <span class="fw-bold text-warning" id="metric-pending-orders">0</span></li>
                        <li class="list-group-item">Nuevos Usuarios (Últimos 7 días): <span class="fw-bold text-primary" id="metric-new-users">0</span></li>
                        <li class="list-group-item">Productos con Bajo Stock: <span class="fw-bold text-danger" id="metric-low-stock-products">0</span></li>
                        <li class="list-group-item text-muted small" id="metric-status-message">Cargando métricas...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Resumen de Pedidos por Estado</h5>
                </div>
                <div class="card-body">
                    <div class="text-center" id="loading-order-status">
                        <div class="spinner-border text-secondary" role="status">
                            <span class="visually-hidden">Cargando resumen de pedidos...</span>
                        </div>
                        <p class="mt-2">Cargando datos...</p>
                    </div>
                    <canvas id="orderStatusChart" style="max-height: 300px;"></canvas>
                    <div id="order-status-message" class="mt-3 text-center"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Generar Reporte de Ventas por Fechas</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="report-start-date" class="form-label">Fecha de Inicio:</label>
                            <input type="date" class="form-control" id="report-start-date">
                        </div>
                        <div class="col-md-4">
                            <label for="report-end-date" class="form-label">Fecha de Fin:</label>
                            <input type="date" class="form-control" id="report-end-date">
                        </div>
                        <div class="col-md-4 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary me-2" id="generate-sales-report-btn">Generar Reporte</button>
                            <button type="button" class="btn btn-danger" id="download-sales-report-pdf-btn" style="display: none;">Descargar PDF</button>
                        </div>
                    </div>
                    <div id="sales-report-container" class="mt-4">
                        <div class="text-center" id="loading-sales-report" style="display: none;">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Cargando reporte de ventas...</span>
                            </div>
                            <p class="mt-2">Cargando reporte...</p>
                        </div>
                        <div id="sales-report-content">
                            <p class="text-muted text-center">Selecciona un rango de fechas y haz clic en "Generar Reporte".</p>
                        </div>
                        <div id="sales-report-message" class="mt-3 text-center"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../js/dashboard_admin.js"></script>
