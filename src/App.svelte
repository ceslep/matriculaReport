<script lang="ts">
  import { onMount } from 'svelte';
  import SearchBar from './lib/components/SearchBar.svelte';
  import ResultsTable from './lib/components/ResultsTable.svelte';
  import ModalPDF from './lib/components/ModalPDF.svelte';
  import { apiClient } from './lib/api/client';
  import type { Estudiante, ApiError } from './lib/types/student';

  let estudiantes = $state<Estudiante[]>([]);
  let loading = $state(false);
  let error = $state('');
  let searchPerformed = $state(false);
  let seleccionados = $state<string[]>([]);
  
  // Estado del modal
  let showModal = $state(false);
  let pdfUrl = $state('');

  // Parámetros de URL
  const urlParams = new URLSearchParams(window.location.search);
  const codigoInicial = urlParams.get('codigo');
  const isEmbedded = urlParams.get('embedded') === 'true';

  const currentYear = new Date().getFullYear();

  // Buscar automáticamente si hay un código en la URL
  onMount(() => {
    if (codigoInicial) {
      handleSearch(codigoInicial);
    }
  });

  async function handleSearch(criterio: string) {
    loading = true;
    error = '';
    searchPerformed = true;
    
    try {
      estudiantes = await apiClient.buscarEstudiante(criterio);
    } catch (e) {
      const apiError = e as ApiError;
      error = apiError.error || 'Error al buscar estudiantes';
      estudiantes = [];
    } finally {
      loading = false;
    }
  }

  function handlePdfClick(codigo: string) {
    pdfUrl = apiClient.getPdfUrl(codigo);
    showModal = true;
  }

  function closeModal() {
    showModal = false;
    pdfUrl = '';
  }

  function handleSeleccionar(codigo: string) {
    if (seleccionados.includes(codigo)) {
      seleccionados = seleccionados.filter(c => c !== codigo);
    } else {
      seleccionados = [...seleccionados, codigo];
    }
  }

  function handleSeleccionarTodos(seleccionar: boolean) {
    if (seleccionar) {
      seleccionados = estudiantes.map(e => e.codigo);
    } else {
      seleccionados = [];
    }
  }

  async function generarPdfConsolidado() {
    if (seleccionados.length === 0) return;
    await apiClient.generarPdfConsolidado(seleccionados);
  }
</script>

<main class="min-h-screen bg-gray-100 py-8">
  <div class="container mx-auto px-4">
    {#if !isEmbedded}
      <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
        Imprimir Información del Estudiante {currentYear}
      </h1>
    {:else}
      <h1 class="text-xl font-bold text-center mb-4 text-gray-800">
        Buscar Estudiante - {currentYear}
      </h1>
    {/if}

    <div class="max-w-3xl mx-auto mb-8">
      <SearchBar onSearch={handleSearch} {loading} />
    </div>

    {#if error}
      <div class="max-w-3xl mx-auto mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <strong class="font-bold">Error:</strong> {error}
      </div>
    {/if}

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <ResultsTable 
        {estudiantes} 
        onPdfClick={handlePdfClick}
        {seleccionados}
        onSeleccionar={handleSeleccionar}
        onSeleccionarTodos={handleSeleccionarTodos}
      />
    </div>

    {#if seleccionados.length > 0}
      <div class="fixed bottom-6 right-6 flex items-center gap-4 bg-white shadow-lg rounded-lg p-4 border border-gray-200">
        <span class="text-sm font-medium text-gray-700">
          {seleccionados.length} estudiante{seleccionados.length !== 1 ? 's' : ''} seleccionado{seleccionados.length !== 1 ? 's' : ''}
        </span>
        <button
          onclick={generarPdfConsolidado}
          class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          PDF Consolidado
        </button>
      </div>
    {/if}
  </div>
</main>

<ModalPDF 
  isOpen={showModal} 
  pdfUrl={pdfUrl} 
  titulo="Vista Previa - Registro de Matrícula"
  onClose={closeModal} 
/>
