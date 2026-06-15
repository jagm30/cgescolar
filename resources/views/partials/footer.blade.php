<style>
    /* ==========================================
       NUEVA IDENTIDAD VISUAL: KOTAN ESCOLAR
       ========================================== */
       
    /* 1. Barra de Navegación Superior (Navbar) */
    .skin-blue .main-header .navbar {
        background-color: #0b3c50 !important; /* Azul oscuro elegante */
    }
    .skin-blue .main-header .logo {
        background-color: #082b3a !important; /* Un poco más oscuro para el logo */
        color: #ffffff !important;
        border-bottom: 0 solid transparent;
    }
    .skin-blue .main-header .logo:hover {
        background-color: #061f2a !important;
    }
    .skin-blue .main-header .navbar .sidebar-toggle:hover {
        background-color: #082b3a !important;
    }

    /* 2. Fondo del Menú Lateral (Sidebar) */
    .skin-blue .main-sidebar, .skin-blue .left-side {
        background-color: #0b3c50 !important;
    }

    /* 3. Color base de los enlaces del menú */
    .skin-blue .sidebar-menu>li>a {
        color: #8aa4af !important; /* Gris azulado para que no canse la vista */
    }

    /* 4. Hover y Elemento Activo en el Sidebar */
    .skin-blue .sidebar-menu>li:hover>a, 
    .skin-blue .sidebar-menu>li.active>a, 
    .skin-blue .sidebar-menu>li.menu-open>a {
        color: #ffffff !important;
        background: #082b3a !important; /* Fondo resaltado */
        border-left-color: #48c4a1 !important; /* Rayita color menta de Kotan */
    }

    /* 5. Color de los iconos al hacer Hover/Activo */
    .skin-blue .sidebar-menu>li:hover>a>i, 
    .skin-blue .sidebar-menu>li.active>a>i {
        color: #48c4a1 !important; /* Iconos color menta */
    }

    /* 6. Submenús desplegables */
    .skin-blue .sidebar-menu .treeview-menu {
        background: #061f2a !important; /* Fondo más profundo para diferenciar */
    }
    .skin-blue .sidebar-menu .treeview-menu>li>a {
        color: #8aa4af !important;
    }
    .skin-blue .sidebar-menu .treeview-menu>li.active>a, 
    .skin-blue .sidebar-menu .treeview-menu>li>a:hover {
        color: #ffffff !important;
    }
    .skin-blue .sidebar-menu .treeview-menu>li.active>a>i, 
    .skin-blue .sidebar-menu .treeview-menu>li>a:hover>i {
        color: #48c4a1 !important;
    }

    /* 7. Cabeceras de sección (FAMILIA, CONFIGURACIÓN, ALUMNOS...) */
    .skin-blue .sidebar-menu>li.header {
        color: #48c4a1 !important; /* Letras menta */
        background: #061f2a !important;
        font-weight: 700;
        letter-spacing: 1px;
    }
    
    /* 8. Panel de usuario arriba del sidebar */
    .skin-blue .sidebar-form, .skin-blue .user-panel {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .skin-blue .user-panel>.info, .skin-blue .user-panel>.info>a {
        color: #ffffff;
    }
</style>
<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Versión</b> 1.0
    </div>
    <strong>Copyright &copy; {{ date('Y') }} KotanEscolar.</strong> Todos los derechos reservados.
</footer>