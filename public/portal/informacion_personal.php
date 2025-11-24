<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: admin.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Portal Académico - Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
<style>
body { font-family: 'Inter', sans-serif; }

.nav-item { transition: all 0.3s ease; position: relative; cursor: pointer; }
.nav-item:hover { transform: translateY(-2px); }
.nav-item.active { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; }
.nav-item.active::before {
    content: '';
    position: absolute; bottom: -2px; left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid #f8fafc;
}

.content-section { display: none; }
.content-section.active { display: block; animation: fadeIn 0.4s ease-in; }

@keyframes fadeIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }

.subject-card { transition: all 0.3s ease; cursor: pointer; }
.subject-card:hover { transform: scale(1.02); box-shadow:0 10px 25px -5px rgba(0,0,0,0.1); }

.dropdown { display: none; }
.dropdown.show { display: block; animation: dropdownFade 0.2s ease-out; }
@keyframes dropdownFade { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }

/* Estilos para el menú desplegable de materias */
.contenidos-list {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
    opacity: 0;
}

.contenidos-list.show {
    max-height: 500px;
    opacity: 1;
    transition: max-height 0.5s ease-in, opacity 0.3s ease-in;
}

.contenido-item {
    border-left: 3px solid #3b82f6;
    background: #f8fafc;
    margin: 8px 0;
    padding: 12px;
    border-radius: 6px;
    transition: transform 0.2s ease;
}

.contenido-item:hover {
    transform: translateX(4px);
}

.rotate-180 {
    transform: rotate(180deg);
}
</style>
</head>
<body class="bg-gray-50 min-h-screen">

<header class="bg-white shadow-sm border-b border-gray-200 p-4 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center">🎓</div>
        <h1 class="text-xl font-semibold text-gray-900">Portal Académico</h1>
    </div>
    <div class="relative">
        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-100">
            <span><?=htmlspecialchars($user['nombre'])?></span>
            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div id="user-dropdown" class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Mi Perfil</a>
            <hr class="my-2">
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Cerrar Sesión</a>
        </div>
    </div>
</header>

<!-- Navigation -->
<nav class="bg-white shadow-sm p-4 flex space-x-2">
    <div class="nav-item active px-4 py-2 rounded" onclick="showSection(event, 'malla')">Malla Curricular</div>
    <div class="nav-item px-4 py-2 rounded" onclick="showSection(event, 'recomendaciones')">Recomendaciones</div>
    <div class="nav-item px-4 py-2 rounded" onclick="showSection(event, 'guia')">Guía Académica</div>
    <div class="nav-item px-4 py-2 rounded" onclick="showSection(event, 'informacion')">Información</div>
</nav>

<!-- Content Sections -->
<main class="max-w-7xl mx-auto p-4 space-y-6">
    <!-- Malla Curricular -->
    <section id="malla" class="content-section active">
        <h2 class="text-2xl font-bold mb-4">Malla Curricular</h2>
        <p>Contenido de la malla curricular aquí (accesos en proceso).</p>
    </section>

    <!-- Recomendaciones -->
    <section id="recomendaciones" class="content-section">
        <h2 class="text-2xl font-bold mb-4">Recomendaciones de Materias</h2>
        <p>Contenido de recomendaciones de materias aquí.</p>
    </section>

    <!-- Guía Académica -->
    <section id="guia" class="content-section">
        <h2 class="text-2xl font-bold mb-4">Guía Académica</h2>
        <input type="text" id="buscador-materias" placeholder="Buscar materia..." 
               class="mb-4 w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <div id="materias-container" class="space-y-4">
            <p class="text-gray-500">Cargando materias...</p>
        </div>
    </section>

    <!-- Información -->
    <section id="informacion" class="content-section">
        <h2 class="text-2xl font-bold mb-4">Información Personal</h2>
        <div class="grid md:grid-cols-2 gap-6 bg-white p-6 rounded shadow-sm border border-gray-200">
            <div>
                <p><strong>Nombre:</strong> <?=htmlspecialchars($user['nombre'])?></p>
                <p><strong>Número:</strong> <?=htmlspecialchars($user['numero'])?></p>
                <p><strong>Carrera:</strong> <?=htmlspecialchars($user['carrera'])?></p>
            </div>
            <div>
                <p><strong>Correo Institucional:</strong> <?=htmlspecialchars($user['correoInstitucional'])?></p>
                <p><strong>Correo Personal:</strong> <?=htmlspecialchars($user['email'])?></p>
                <p><strong>Teléfono:</strong> +52 <?=htmlspecialchars($user['telefono'])?></p>
                <p><strong>Estado Académico:</strong> <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm"><?=htmlspecialchars($user['estado'])?></span></p>
            </div>
        </div>
    </section>
</main>

<script>
// Esperar a que cargue el CDN de Supabase
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que Supabase esté disponible
    if (typeof supabase === 'undefined') {
        console.error('Supabase no está cargado. Verifica el CDN.');
        return;
    }
    
    // Inicializar Supabase
    const supabaseUrl = "https://vxorllitblvcfjtofwdl.supabase.co";
    const supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ4b3JsbGl0Ymx2Y2ZqdG9md2RsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4ODg0NDYsImV4cCI6MjA3NjQ2NDQ0Nn0.5RztDY6R6szpUjIl6OB6fYZhXnTlvIikvNrKHcHXPhc";
    const supabaseClient = supabase.createClient(supabaseUrl, supabaseKey);
    
    // Estado global
    window.todasLasMaterias = [];
    window.materiasCargadas = new Set();
    window.supabase = supabaseClient;

    // Solo cargar materias si estamos en la sección correcta
    if (document.getElementById('guia').classList.contains('active')) {
        cargarMaterias();
    }
});

// Funciones de navegación
function showSection(event, id){
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    // Si es la sección de guía, cargar materias
    if (id === 'guia') {
        cargarMaterias();
    }
}

function toggleUserMenu(){
    document.getElementById('user-dropdown').classList.toggle('show');
}

// Cerrar menús al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('#user-dropdown') && !e.target.closest('button[onclick="toggleUserMenu()"]')) {
        document.getElementById('user-dropdown').classList.remove('show');
    }
});

// Función para cargar materias
async function cargarMaterias() {
    const container = document.getElementById("materias-container");
    container.innerHTML = `<p class="text-gray-500">Cargando materias...</p>`;

    try {
        // Verificar que supabase esté disponible
        if (!window.supabase) {
            throw new Error('Supabase no está inicializado');
        }

        const { data, error } = await window.supabase
            .from("asignaturas")
            .select("Materia, Clave")
            .order("Materia", { ascending: true });

        if (error) throw error;

        if (!data || data.length === 0) {
            container.innerHTML = `<p class="text-gray-500">No hay materias disponibles</p>`;
            return;
        }

        window.todasLasMaterias = data;
        mostrarMaterias(data);

    } catch (err) {
        console.error("Error cargando materias:", err);
        container.innerHTML = `<p class="text-red-500">Error al cargar materias: ${err.message}</p>`;
    }
}

// Mostrar materias con menú desplegable
function mostrarMaterias(materias) {
    const container = document.getElementById("materias-container");
    
    if (materias.length === 0) {
        container.innerHTML = `<p class="text-gray-500">No se encontraron materias</p>`;
        return;
    }

    container.innerHTML = materias.map(m => `
        <div class="subject-card border border-gray-200 rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-center cursor-pointer" onclick="toggleContenidos('${m.Clave}')">
                <div>
                    <h3 class="font-semibold text-lg text-gray-800">${m.Materia}</h3>
                    <p class="text-gray-500 text-sm">Clave: ${m.Clave}</p>
                </div>
                <svg id="icon-${m.Clave}" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
            <div id="contenidos-${m.Clave}" class="contenidos-list mt-3">
                <div class="text-gray-500 text-center py-2">
                    <p>Cargando contenidos...</p>
                </div>
            </div>
        </div>
    `).join('');
}

// Mostrar/ocultar contenidos de la materia seleccionada
async function toggleContenidos(clave) {
    const contenedor = document.getElementById(`contenidos-${clave}`);
    const icono = document.getElementById(`icon-${clave}`);
    
    // Toggle visual
    contenedor.classList.toggle('show');
    icono.classList.toggle('rotate-180');
    
    // Cargar contenidos si no se han cargado antes
    if (contenedor.classList.contains('show') && !window.materiasCargadas.has(clave)) {
        await cargarContenidos(clave);
        window.materiasCargadas.add(clave);
    }
}

// Cargar contenidos desde Supabase
async function cargarContenidos(clave) {
    const contenedor = document.getElementById(`contenidos-${clave}`);
    
    try {
        // Mostrar loading
        contenedor.innerHTML = `<div class="text-gray-500 text-center py-2"><p>Cargando contenidos...</p></div>`;

        const { data, error } = await window.supabase
            .from("Contenidos")
            .select("Titulo, Contenidos, created_at")
            .eq("ClaveMateria", clave)
            .order("created_at", { ascending: false });

        if (error) throw error;

        if (!data || data.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No hay contenidos registrados para esta materia.</p>
                </div>
            `;
            return;
        }

        // Mostrar contenidos
        contenedor.innerHTML = `
            <div class="space-y-3">
                ${data.map(c => `
                    <div class="contenido-item">
                        <h4 class="font-semibold text-gray-800 mb-1">${c.Titulo}</h4>
                        <div class="flex justify-between items-center">
                            <a href="${c.Contenidos}" target="_blank" 
                               class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Ver Contenido
                            </a>
                            <span class="text-xs text-gray-500">
                                ${new Date(c.created_at).toLocaleDateString('es-ES')}
                            </span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

    } catch (err) {
        console.error("Error cargando contenidos:", err);
        contenedor.innerHTML = `
            <div class="text-center py-4 text-red-500">
                <p>Error al cargar los contenidos</p>
            </div>
        `;
    }
}

// Filtro de búsqueda
document.getElementById('buscador-materias').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    
    if (term === '') {
        mostrarMaterias(window.todasLasMaterias);
        return;
    }

    const filtradas = window.todasLasMaterias.filter(m => 
        m.Materia.toLowerCase().includes(term) || 
        m.Clave.toLowerCase().includes(term)
    );
    
    mostrarMaterias(filtradas);
});
</script>
</body>
</html>