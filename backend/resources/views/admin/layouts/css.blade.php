<link href="{{ asset('dist/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('dist/css/sb-admin-2.min.css') }}" rel="stylesheet">

    
<style>
    /* =========================
   PRESENTRI MODERN BLUE THEME
========================= */

:root{
    --primary:#2563eb;
    --primary-dark:#1d4ed8;
    --sidebar-dark:#0f172a;
    --sidebar-hover:#1e293b;
}

/* BODY */
body{
    background:#f8fafc !important;
}

/* ===================================
   MODERN SIDEBAR PRESENTRI
=================================== */

.app-sidebar {
    width: 280px !important;
    min-height: 100vh;
    background: #ffffff !important;
    border-right: 1px solid #edf0f5;
    padding: 18px 14px;
    box-shadow: 0 0 40px rgba(0,0,0,.04);
}

/* Logo */

.sidebar-brand {
    display: flex;
    align-items: center;
    text-decoration: none !important;
    padding: 12px;
    margin-bottom: 25px;
}

.brand-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(
        135deg,
        #6366f1,
        #8b5cf6
    );
    color: white;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;

    box-shadow: 0 10px 25px rgba(99,102,241,.25);
}

.brand-content {
    margin-left: 12px;
}

.brand-content h5 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #111827;
}

.brand-content small {
    color: #9ca3af;
    font-size: 12px;
}

/* Section Title */

.sidebar-title {
    margin-top: 18px;
    margin-bottom: 10px;
    padding: 0 14px;

    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;

    color: #9ca3af;
}

/* Menu */

.app-sidebar .nav-item {
    margin-bottom: 6px;
}

.app-sidebar .nav-link {
    position: relative;

    display: flex;
    align-items: center;

    padding: 12px 14px !important;

    border-radius: 14px;

    color: #4b5563 !important;
    font-weight: 500;

    transition: all .25s ease;
}

/* Icon Box */

.nav-icon {
    width: 38px;
    height: 38px;

    border-radius: 12px;

    background: #f3f4f6;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-right: 12px;

    color: #6b7280;
    transition: all .25s ease;
}

/* Hover */

.app-sidebar .nav-link:hover {
    background: #f8fafc;
    color: #111827 !important;
    transform: translateX(4px);
}

.app-sidebar .nav-link:hover .nav-icon {
    background: #eef2ff;
    color: #6366f1;
}

/* Active */

.app-sidebar .nav-item.active .nav-link {
    background: #eef2ff;
    color: #4f46e5 !important;
    font-weight: 600;
}

.app-sidebar .nav-item.active .nav-icon {
    background: #6366f1;
    color: white;
}

.app-sidebar .nav-item.active .nav-link::before {
    content: "";

    position: absolute;

    left: -14px;
    top: 10px;

    width: 4px;
    height: 60%;

    border-radius: 20px;

    background: #6366f1;
}

/* Footer */

.sidebar-footer {
    margin-top: auto;
    padding-top: 25px;
}

.sidebar-footer button {
    width: 100%;
    height: 45px;

    border: none;
    border-radius: 12px;

    background: #f3f4f6;
    color: #6b7280;

    transition: .25s;
}

.sidebar-footer button:hover {
    background: #6366f1;
    color: white;
}

/* SB Admin Fix */

.app-sidebar .nav-link span {
    font-size: 14px;
}

.app-sidebar .nav-link i {
    font-size: 15px;
}

.app-sidebar hr {
    display: none;
}

/* =========================
   TOPBAR
========================= */

/* ===================================
   TOPBAR MODERN
=================================== */

.topbar-modern{
    height: 80px;
    background: #ffffff;

    border-bottom: 1px solid #edf0f5;

    padding: 0 30px;

    box-shadow:
        0 1px 2px rgba(0,0,0,.03);

    display:flex;
    align-items:center;
}

/* LEFT */

.topbar-left{
    display:flex;
    flex-direction:column;
}

.page-title{
    font-size:22px;
    font-weight:700;
    color:#111827;
    line-height:1;
}

.page-subtitle{
    margin-top:5px;

    font-size:13px;
    color:#9ca3af;
}

/* MOBILE BUTTON */

.sidebar-toggle-mobile{
    width:42px;
    height:42px;

    border:none;
    border-radius:12px;

    background:#f3f4f6;
    color:#6b7280;

    margin-right:15px;
}

.sidebar-toggle-mobile:hover{
    background:#eef2ff;
    color:#6366f1;
}

/* USER INFO */

.user-info-modern{
    display:flex;
    align-items:center;
}

.user-avatar{
    width:42px;
    height:42px;

    border-radius:12px;

    background:linear-gradient(
        135deg,
        #6366f1,
        #8b5cf6
    );

    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:700;

    box-shadow:
        0 8px 20px rgba(99,102,241,.25);
}

.user-detail{
    margin-left:12px;
}

.user-detail h6{
    margin:0;
    font-size:14px;
    font-weight:600;
    color:#111827;
}

.user-detail small{
    color:#9ca3af;
    font-size:12px;
}

/* LOGOUT */

.btn-logout-modern{
    height:42px;

    border:none;

    border-radius:12px;

    background:#f3f4f6;

    color:#6b7280;

    padding:0 18px;

    display:flex;
    align-items:center;
    gap:8px;

    transition:.25s;
}

.btn-logout-modern:hover{
    background:#fee2e2;
    color:#dc2626;
}

/* RESPONSIVE */

@media(max-width:768px){

    .topbar-modern{
        padding:0 15px;
    }

    .page-title{
        font-size:18px;
    }

    .page-subtitle{
        display:none;
    }
}

/* =========================
   CARD
========================= */

.card{
    border:none !important;
    border-radius:18px !important;
    box-shadow:0 2px 12px rgba(15,23,42,.05) !important;
}

.card-header{
    background:white !important;
    border-bottom:1px solid #f1f5f9 !important;
}

/* =========================
   TABLE
========================= */

.table{
    background:white;
}

.table thead th{
    background:#eff6ff;
    color:#1e40af;
    border:none !important;
    font-weight:600;
}

.table td,
.table th{
    vertical-align:middle;
}

/* =========================
   BUTTONS
========================= */

.btn-primary{
    background:#2563eb !important;
    border-color:#2563eb !important;
    border-radius:10px;
}

.btn-primary:hover{
    background:#1d4ed8 !important;
    border-color:#1d4ed8 !important;
}

/* =========================
   FOOTER
========================= */

.sticky-footer{
    background:#fff !important;
    border-top:1px solid #e5e7eb;
}

.sticky-footer span{
    color:#64748b;
    font-size:14px;
}

/* =========================
   SCROLLBAR
========================= */

::-webkit-scrollbar{
    width:8px;
}

::-webkit-scrollbar-thumb{
    background:#2563eb;
    border-radius:20px;
}

::-webkit-scrollbar-track{
    background:#f1f5f9;
}

body,
.sidebar,
.topbar,
.card,
.table,
.btn{
    font-family: 'Poppins', sans-serif !important;
}
</style>
