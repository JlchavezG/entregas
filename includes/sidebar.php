<aside class="sidebar">
    <div class="logo">
        <span class="logo-text">DeliveryApp</span>
        <button class="theme-toggle" id="themeToggle">
            <i class="fas fa-moon" id="themeIcon"></i>
        </button>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left" id="sidebarIcon"></i>
        </button>
    </div>
    <ul class="sidebar-nav">
         <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> <span class="nav-text">Dashboard</span>
        </a></li>
        <li><a href="entregas.php" class="<?= basename($_SERVER['PHP_SELF']) === 'entregas.php' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> <span class="nav-text">Entregas</span>
        </a></li>
        <li><a href="asignar.php" class="<?= basename($_SERVER['PHP_SELF']) === 'asignar.php' ? 'active' : '' ?>">
            <i class="fas fa-plus"></i> <span class="nav-text">Nueva Entrega</span>
        </a></li>
        <li><a href="repartidores.php" class="<?= basename($_SERVER['PHP_SELF']) === 'repartidores.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span class="nav-text">Repartidores</span>
        </a></li>
    </ul>
</aside>