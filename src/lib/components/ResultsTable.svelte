<script lang="ts">
  import type { Estudiante } from '../types/student';
  import { apiClient } from '../api/client';

  let { 
    estudiantes = [], 
    onPdfClick,
    seleccionados = [],
    onSeleccionar,
    onSeleccionarTodos
  }: { 
    estudiantes: Estudiante[]; 
    onPdfClick?: (codigo: string) => void;
    seleccionados?: string[];
    onSeleccionar?: (codigo: string) => void;
    onSeleccionarTodos?: (seleccionar: boolean) => void;
  } = $props();

  function getGrupo(estudiante: Estudiante): string {
    return `${estudiante.nivel}-${estudiante.numero}`;
  }

  function descargarPdf(codigo: string) {
    if (onPdfClick) {
      onPdfClick(codigo);
    } else {
      apiClient.descargarPdf(codigo);
    }
  }

  function handleSeleccionar(codigo: string) {
    if (onSeleccionar) {
      onSeleccionar(codigo);
    }
  }

  function handleSeleccionarTodos() {
    if (onSeleccionarTodos) {
      const allSelected = estudiantes.every(e => seleccionados.includes(e.codigo));
      onSeleccionarTodos(!allSelected);
    }
  }

  const todosSeleccionados = $derived(
    estudiantes.length > 0 && estudiantes.every(e => seleccionados.includes(e.codigo))
  );
</script>

<div class="overflow-x-auto">
  <table class="w-full border-collapse">
    <thead>
      <tr class="bg-gray-800 text-white">
        <th class="px-4 py-3 text-center w-12">
          <input 
            type="checkbox" 
            checked={todosSeleccionados}
            onchange={handleSeleccionarTodos}
            class="w-4 h-4 cursor-pointer"
          />
        </th>
        <th class="px-4 py-3 text-center">N°</th>
        <th class="px-4 py-3 text-center">Código</th>
        <th class="px-4 py-3">Estudiante</th>
        <th class="px-4 py-3">Nombres</th>
        <th class="px-4 py-3 text-center">Grupo</th>
        <th class="px-4 py-3 text-center">Sede</th>
        <th class="px-4 py-3 text-center">PDF</th>
      </tr>
    </thead>
    <tbody>
      {#if estudiantes.length === 0}
        <tr>
          <td colspan="8" class="px-4 py-8 text-center text-gray-500">
            Realiza una búsqueda
          </td>
        </tr>
      {:else}
        {#each estudiantes as estudiante, index}
          <tr class="border-b hover:bg-gray-50" class:bg-blue-50={seleccionados.includes(estudiante.codigo)}>
            <td class="px-4 py-3 text-center">
              <input 
                type="checkbox" 
                checked={seleccionados.includes(estudiante.codigo)}
                onchange={() => handleSeleccionar(estudiante.codigo)}
                class="w-4 h-4 cursor-pointer"
              />
            </td>
            <td class="px-4 py-3 text-center">{index + 1}</td>
            <td class="px-4 py-3 text-center">{estudiante.codigo}</td>
            <td class="px-4 py-3">{estudiante.estudiante}</td>
            <td class="px-4 py-3">{estudiante.nombres}</td>
            <td class="px-4 py-3 text-center">{getGrupo(estudiante)}</td>
            <td class="px-4 py-3 text-center">{estudiante.sede}</td>
            <td class="px-4 py-3 text-center">
              <button
                onclick={() => descargarPdf(estudiante.codigo)}
                class="inline-flex items-center justify-center px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors"
                title="Descargar PDF"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v6m-3-3h6" />
                </svg>
                PDF
              </button>
            </td>
          </tr>
        {/each}
      {/if}
    </tbody>
  </table>
</div>
