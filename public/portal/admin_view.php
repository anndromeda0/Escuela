<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: admin.php');
    exit;
}
$admin = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Panel Administrativo - Portal Académico</title>
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
.dropdown { display: none; }
.dropdown.show { display: block; animation: dropdownFade 0.2s ease-out; }
@keyframes dropdownFade { from {opacity:0; transform:translateY(-10px);} to {opacity:1; transform:translateY(0);} }
</style>
</head>

<!-- Modal agregar/editar materia -->
<div id="modal-materia" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-96 shadow-lg">
        <h2 id="modal-title" class="text-xl font-bold mb-4">Agregar Materia</h2>

        <!-- guardamos la clave original si editamos -->
        <input type="hidden" id="materia-original">

        <label class="block text-sm font-medium">Materia</label>
        <input id="materia-nombre" class="w-full border rounded p-2 mb-3" />

        <label class="block text-sm font-medium">Clave</label>
        <input id="materia-clave" class="w-full border rounded p-2 mb-3" />

        <label class="block text-sm font-medium">Carrera</label>
        <input id="materia-carrera" class="w-full border rounded p-2 mb-4" />

        <div class="flex justify-end space-x-2">
            <button onclick="cerrarModalMateria()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
            <button onclick="guardarMateria()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal confirmar eliminación materia -->
<div id="modal-eliminar" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-80 shadow-lg">
        <h2 class="text-lg font-bold mb-4">Confirmar eliminación</h2>
        <p class="mb-4">¿Seguro que deseas eliminar esta materia?</p>

        <div class="flex justify-end space-x-2">
            <button onclick="cerrarModalEliminar()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
            <button id="btn-confirmar-eliminar" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
        </div>
    </div>
</div>

<!-- Modal Editar Alumno -->
<div id="modal-alumno" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-lg w-96">

        <h2 id="modal-alumno-title" class="text-xl font-bold mb-4">Editar Alumno</h2>

        <input type="hidden" id="alumno-id">

        <label class="block mb-2">Nombre:</label>
        <input id="alumno-nombre" class="w-full border px-3 py-2 rounded mb-3">

        <label class="block mb-2">Carrera:</label>
        <input id="alumno-carrera" class="w-full border px-3 py-2 rounded mb-3">

        <label class="block mb-2">Correo:</label>
        <input id="alumno-correo" class="w-full border px-3 py-2 rounded mb-3">

        <label class="block mb-2">Estado:</label>
        <select id="alumno-estado" class="w-full border px-2 py-2 rounded mb-4">
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
        </select>

        <div class="flex justify-end gap-3">
            <button class="px-4 py-2 bg-gray-300 rounded" onclick="cerrarModalAlumno()">Cancelar</button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded" onclick="guardarAlumno()">Guardar</button>
        </div>

    </div>
</div>


<!-- Modal Eliminar Alumno -->
<div id="modal-eliminar-alumno" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-xl w-80">
        <h2 class="text-lg font-bold mb-3">Eliminar Alumno</h2>
        <p class="mb-4">¿Seguro que deseas eliminar este alumno?</p>

        <div class="flex justify-end space-x-2">
            <button class="px-4 py-2 bg-gray-300 rounded" onclick="cerrarModalEliminarAlumno()">Cancelar</button>
            <button id="btn-confirmar-eliminar-alumno" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
        </div>
    </div>
</div>

<body class="bg-gray-50 min-h-screen">
 
<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200 p-4 flex justify-between items-center">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-indigo-600 text-white rounded-lg flex items-center justify-center font-bold">🛠️</div>
        <h1 class="text-xl font-semibold text-gray-900">Panel Administrativo</h1>
    </div>
    <div class="relative">
        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 p-2 rounded hover:bg-gray-100">
            <span><?=htmlspecialchars($admin['nombre'] ?? 'Administrador')?></span>
            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div id="user-dropdown" class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Perfil</a>
            <hr class="my-2">
            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Cerrar Sesión</a>
        </div>
    </div>
</header>

<!-- Navigation -->
<nav class="bg-white shadow-sm p-4 flex space-x-2">
    <div class="nav-item active px-4 py-2 rounded" onclick="showSection('contenido', event)">Subir Contenido</div>
    <div class="nav-item px-4 py-2 rounded" onclick="loadAlumnos(event)">Administrar Alumnos</div>
    <div class="nav-item px-4 py-2 rounded" onclick="loadMaterias(event)">Administrar Materias</div>
</nav>

<!-- Main Content -->
<main class="max-w-6xl mx-auto p-4 space-y-6">

    <!-- Subir Contenido -->
    <section id="contenido" class="content-section active">
        <h2 class="text-3xl font-bold mb-6 text-gray-800">Subir Contenido por Materia</h2>

        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 max-w-6xl">
            <form id="form-subir" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Selecciona la materia</label>
                    <select name="materia" class="w-full border rounded p-2" onfocus="cargarMateriasSelect()">
                        <option value="">-- Selecciona una materia --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Título del contenido</label>
                    <input id="input-titulo" type="text" class="w-full border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Archivo</label>
                    <input id="input-archivo" type="file" class="w-full border-gray-300 rounded-lg p-3 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <button type="submit" class="bg-blue-600 text-white w-full py-3 rounded-lg font-semibold hover:bg-blue-700 shadow-md transition">
                    Subir Archivo
                </button>
                <p id="estado-subida" class="mt-2 text-sm font-medium"></p>
            </form>
        </div>
    </section>

    <!-- Administrar Alumnos -->
    <section id="alumnos" class="content-section">
        <h2 class="text-2xl font-bold mb-4">Administrar Alumnos</h2>
        <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Número</th>
                        <th class="border p-2">Nombre</th>
                        <th class="border p-2">Carrera</th>
                        <th class="border p-2">Correo</th>
                        <th class="border p-2">Estado</th>
                        <th class="border p-2">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-alumnos"></tbody>
            </table>
        </div>
    </section>

    <!-- Administrar Materias -->
    <section id="materias" class="content-section">
        <h2 class="text-2xl font-bold mb-4">Administrar Materias</h2>
        <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
            <button onclick="abrirModalMateria()" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
                Agregar Materia
            </button>
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2">Clave</th>
                        <th class="border p-2">Materia</th>
                        <th class="border p-2">Carrera</th>
                        <th class="border p-2">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-materias">
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">Haz clic en "Administrar Materias" para cargar datos.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
<select id="form-subir" class="hidden" data-codigo="<?= htmlspecialchars($admin['numero']) ?>">

</main>

<script>
const supabaseUrl = "https://vxorllitblvcfjtofwdl.supabase.co";
const supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InZ4b3JsbGl0Ymx2Y2ZqdG9md2RsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjA4ODg0NDYsImV4cCI6MjA3NjQ2NDQ0Nn0.5RztDY6R6szpUjIl6OB6fYZhXnTlvIikvNrKHcHXPhc";
const supabase = window.supabase.createClient(supabaseUrl, supabaseKey);

function showSection(id, event) {
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    if (event && event.currentTarget) event.currentTarget.classList.add('active');
}

function toggleUserMenu() {
    document.getElementById('user-dropdown').classList.toggle('show');
}

// ======================= MATERIAS =======================
async function loadMaterias(event) {
    showSection('materias', event);
    const tbody = document.getElementById('tabla-materias');
    tbody.innerHTML = `<tr><td colspan="4" class="p-4 text-center text-gray-500">Cargando materias...</td></tr>`;

    try {
        const { data: materias, error } = await supabase
            .from('asignaturas')
            .select('*')
            .order('Materia', { ascending: true });

        if (error) throw error;

        if (!materias || materias.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">No hay materias registradas.</td>
                </tr>`;
            return;
        }

        tbody.innerHTML = materias.map(m => `
            <tr class="hover:bg-gray-50">
                <td class="border p-2 text-center">${escapeHtml(m.Clave)}</td>
                <td class="border p-2">${escapeHtml(m.Materia)}</td>
                <td class="border p-2">${escapeHtml(m.Carrera)}</td>
                <td class="border p-2 text-center space-x-2">
                    <button class="bg-green-800 hover:bg-green-700 text-white font-bold py-2 px-4 border-b-4 border-green-900 hover:border-green-700 rounded" onclick="editarMateria('${escapeJs(m.Clave)}')">
                        <div class="flex items-center gap-2"><span>Editar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </div>
                    </button>
                    <button class="bg-red-800 hover:bg-red-700 text-white font-bold py-2 px-4 border-b-4 border-red-900 hover:border-red-700 rounded" onclick="eliminarMateria('${escapeJs(m.Clave)}')">
                        <div class="flex items-center gap-2">
                            <span>Eliminar</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="4" class="p-4 text-center text-red-600">Error al cargar materias: ${escapeHtml(err.message)}</td></tr>`;
    }
}

function abrirModalMateria(data = null) {
    document.getElementById("modal-materia").classList.remove("hidden");

    if (data) {
        document.getElementById("modal-title").textContent = "Editar Materia";
        document.getElementById("materia-original").value = data.Clave || '';
        document.getElementById("materia-clave").value = data.Clave || '';
        document.getElementById("materia-nombre").value = data.Materia || '';
        document.getElementById("materia-carrera").value = data.Carrera || '';
    } else {
        document.getElementById("modal-title").textContent = "Agregar Materia";
        document.getElementById("materia-original").value = '';
        document.getElementById("materia-clave").value = '';
        document.getElementById("materia-nombre").value = '';
        document.getElementById("materia-carrera").value = '';
    }
}

function cerrarModalMateria() {
    document.getElementById("modal-materia").classList.add("hidden");
}

// guardarMateria: si materia-original tiene valor -> UPDATE por Clave original, si no -> INSERT
async function guardarMateria() {
    const original = document.getElementById("materia-original").value.trim();
    const Materia = document.getElementById("materia-nombre").value.trim();
    const Clave = document.getElementById("materia-clave").value.trim();
    const Carrera = document.getElementById("materia-carrera").value.trim();

    if (!Materia || !Clave || !Carrera) {
        alert("Todos los campos son requeridos");
        return;
    }

    try {
        let res;
        if (original) {
            // UPDATE usando la clave original en where
            res = await supabase
                .from("asignaturas")
                .update({ Materia, Clave, Carrera })
                .eq("Clave", original);
        } else {
            // INSERT
            res = await supabase
                .from("asignaturas")
                .insert([{ Materia, Clave, Carrera }]);
        }

        if (res.error) throw res.error;
        cerrarModalMateria();
        loadMaterias({ currentTarget: document.querySelector(".nav-item:nth-child(3)") });
    } catch (err) {
        alert("Error: " + err.message);
    }
}

// editarMateria: obtiene por Clave y abre modal
async function editarMateria(clave) {
    try {
        const { data, error } = await supabase
            .from("asignaturas")
            .select("*")
            .eq("Clave", clave)
            .single();

        if (error) throw error;
        abrirModalMateria(data);
    } catch (err) {
        alert("Error al obtener materia: " + err.message);
    }
}

// eliminarMateria: abre modal de confirmación y guarda la clave a eliminar
let materiaAEliminar = null;
function eliminarMateria(clave) {
    materiaAEliminar = clave;
    document.getElementById("modal-eliminar").classList.remove("hidden");
}

function cerrarModalEliminar() {
    materiaAEliminar = null;
    document.getElementById("modal-eliminar").classList.add("hidden");
}

document.getElementById("btn-confirmar-eliminar").onclick = async () => {
    if (!materiaAEliminar) return;
    try {
        const { error } = await supabase
            .from("asignaturas")
            .delete()
            .eq("Clave", materiaAEliminar);

        if (error) throw error;
        cerrarModalEliminar();
        loadMaterias({ currentTarget: document.querySelector(".nav-item:nth-child(3)") });
    } catch (err) {
        alert("Error: " + err.message);
    }
};

// ======================= ALUMNOS =======================
async function loadAlumnos(event) {
    showSection('alumnos', event);
    const tbody = document.getElementById('tabla-alumnos');
    tbody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Cargando alumnos...</td></tr>`;

    try {
        const { data: alumnos, error } = await supabase
            .from('estudiantes')
            .select('*')
            .order('Nombre', { ascending: true });

        if (error) throw error;

        if (!alumnos || alumnos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-500">
                        No hay alumnos registrados.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = alumnos.map((a) => `
            <tr class="hover:bg-gray-50">
                <td class="border p-2 text-center">${a.NumeroEstudiante}</td>
                <td class="border p-2">${a.Nombre || 'Sin nombre'}</td>
                <td class="border p-2">${a.Carrera || '—'}</td>
                <td class="border p-2">${a.InstitucionalEmail || 'Sin correo'}</td>
                <td class="border p-2 text-center">
                    <span class="${a.Estado === 'Activo' 
                        ? 'px-2 py-1 rounded bg-green-600 text-white' 
                        : 'px-2 py-1 rounded bg-red-600 text-white'}">
                        ${a.Estado}
                    </span>
                </td>
                <td class="border p-2 text-center space-x-2">
                    
                <!-- BOTÓN EDITAR -->
                <button class="bg-green-800 hover:bg-green-700 text-white font-bold py-2 px-4 border-b-4 border-green-900 hover:border-green-700 rounded" onclick="editarAlumno(${a.NumeroEstudiante})">
                    <div class="flex items-center gap-2"><span>Editar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                </button>

                <!-- BOTÓN ELIMINAR -->
                <button class="bg-red-800 hover:bg-red-700 text-white font-bold py-2 px-4 border-b-4 border-red-900 hover:border-red-700 rounded" onclick="eliminarAlumno(${a.NumeroEstudiante})">
                    <div class="flex items-center gap-2">
                        <span>Eliminar</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                </button>

                </td>
            </tr>
        `).join('');
    } catch (err) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="p-4 text-center text-red-600">
                    Error al cargar alumnos: ${err.message}
                </td>
            </tr>`;
    }
}


// editarAlumno: trae alumno por NumeroEstudiante y abre modal
async function editarAlumno(numero) {
    try {
        const { data, error } = await supabase
            .from('estudiantes')
            .select('*')
            .eq('NumeroEstudiante', numero)
            .single();

        if (error) throw error;
        abrirModalAlumno(data);
    } catch (err) {
        alert("Error al obtener alumno: " + err.message);
    }
}

function abrirModalAlumno(data) {
    document.getElementById("modal-alumno").classList.remove("hidden");
    document.getElementById("modal-alumno-title").textContent = "Editar Alumno";
    document.getElementById("alumno-id").value = data.NumeroEstudiante;
    document.getElementById("alumno-nombre").value = data.Nombre || '';
    document.getElementById("alumno-correo").value = data.InstitucionalEmail || '';
    document.getElementById("alumno-carrera").value = data.Carrera || '';
    document.getElementById("alumno-estado").value = data.Estado || 'Inactivo';
}

function cerrarModalAlumno() {
    document.getElementById("modal-alumno").classList.add("hidden");
}

async function guardarAlumno() {
    const id = document.getElementById("alumno-id").value;
    const Nombre = document.getElementById("alumno-nombre").value;
    const Carrera = document.getElementById("alumno-carrera").value;
    const InstitucionalEmail = document.getElementById("alumno-correo").value;
    const Estado = document.getElementById("alumno-estado").value;

    const { error } = await supabase
        .from("estudiantes")
        .update({ Nombre, Carrera, InstitucionalEmail, Estado })
        .eq("NumeroEstudiante", id);

    if (error) {
        alert("Error: " + error.message);
        return;
    }

    cerrarModalAlumno();
    loadAlumnos({ currentTarget: document.querySelector(".nav-item:nth-child(2)") });
}


// eliminar alumno
let alumnoAEliminar = null;

function eliminarAlumno(id) {
    alumnoAEliminar = id;
    document.getElementById("modal-eliminar-alumno").classList.remove("hidden");
}

function cerrarModalEliminarAlumno() {
    alumnoAEliminar = null;
    document.getElementById("modal-eliminar-alumno").classList.add("hidden");
}

document.getElementById("btn-confirmar-eliminar-alumno").onclick = async () => {
    if (!alumnoAEliminar) return;

    const { error } = await supabase
        .from("estudiantes")
        .delete()
        .eq("NumeroEstudiante", alumnoAEliminar);

    if (error) {
        alert("Error al eliminar: " + error.message);
        return;
    }

    cerrarModalEliminarAlumno();
    loadAlumnos({ currentTarget: document.querySelector(".nav-item:nth-child(2)") });
};


// ========== SUBIR CONTENIDOS ==========
async function cargarMateriasSelect() {
    const select = document.querySelector("select[name='materia']");
    if (!select) return;
    select.innerHTML = `<option value="">Cargando materias...</option>`;

    try {
        const { data, error } = await supabase
            .from("asignaturas")
            .select("Materia, Clave")
            .order("Materia", { ascending: true });

        if (error) {
            console.error("Error cargando materias:", error);
            select.innerHTML = `<option value="">Error al cargar</option>`;
            return;
        }

        if (!data || data.length === 0) {
            select.innerHTML = `<option value="">No hay materias registradas</option>`;
            return;
        }

        select.innerHTML = `<option value="">-- Selecciona una materia --</option>` +
            data.map(m => `<option value="${escapeJs(m.Clave)}">${escapeHtml(m.Materia)} (${escapeHtml(m.Clave)})</option>`).join('');
    } catch (err) {
        console.error(err);
        select.innerHTML = `<option value="">Error al cargar</option>`;
    }
}

document.getElementById('form-subir').addEventListener('submit', async (e) => {
    e.preventDefault();
    const estado = document.getElementById('estado-subida');
    estado.textContent = "⏳ Subiendo...";
    estado.className = "mt-2 text-sm font-medium text-blue-600";

    const materia = document.querySelector("select[name='materia']").value;
    const codigo = 218768577;
    const titulo = document.getElementById("input-titulo").value.trim();
    const archivoInput = document.getElementById("input-archivo");
    const archivo = archivoInput.files[0];

    if (!materia || !titulo || !archivo) {
        estado.textContent = "❌ Completa todos los campos";
        estado.className = "mt-2 text-sm font-medium text-red-600";
        return;
    }


        const nombreArchivo = `${archivo.name}`;
        console.log(nombreArchivo);
        // Subir a storage
        const { data: uploadData, error: uploadError } = await supabase.storage
            .from("contenidos")
            .upload(nombreArchivo, archivo);

        if (uploadError) {
            estado.textContent = "❌ Error al subir el archivo: " + uploadError.message;
            estado.className = "mt-2 text-sm font-medium text-red-600";
            return;
        }
        const { data: urlData } = supabase.storage.from("contenidos").getPublicUrl(nombreArchivo);
        const urlPublica = urlData.publicUrl;

        const { error: insertError } = await supabase
            .from("Contenidos")
            .insert([{ CodigoAdmin: codigo,ClaveMateria: materia, Titulo: titulo, Contenidos: urlPublica }]);

        if (insertError) throw insertError;

        estado.textContent = "✅ Archivo subido correctamente";
        estado.className = "mt-2 text-sm font-medium text-green-600";
        document.getElementById('form-subir').reset();

});

// Ejecutar carga inicial del select al cargar la página
document.addEventListener("DOMContentLoaded", () => {
    cargarMateriasSelect();
});

// =================== UTIL helpers ===================

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function escapeJs(str) {
    if (str === null || str === undefined) return '';
    return String(str).replaceAll("'", "\\'");
}
</script>

</body>
</html>
