<script lang="ts">
  import SearchBar from './lib/components/SearchBar.svelte';
  import ResultsTable from './lib/components/ResultsTable.svelte';
  import ModalPDF from './lib/components/ModalPDF.svelte';
  import { apiClient } from './lib/api/client';
  import type { Estudiante, ApiError } from './lib/types/student';

  let estudiantes = $state<Estudiante[]>([]);
  let loading = $state(false);
  let error = $state('');
  let searchPerformed = $state(false);
  
  // Estado del modal
  let showModal = $state(false);
  let pdfUrl = $state('');

  const currentYear = new Date().getFullYear();

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
</script>

<main class="min-h-screen bg-gray-100 py-8">
  <div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">
      Imprimir Información del Estudiante {currentYear}
    </h1>

    <div class="max-w-3xl mx-auto mb-8">
      <SearchBar onSearch={handleSearch} {loading} />
    </div>

    {#if error}
      <div class="max-w-3xl mx-auto mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
        <strong class="font-bold">Error:</strong> {error}
      </div>
    {/if}

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
      <ResultsTable {estudiantes} onPdfClick={handlePdfClick} />
    </div>
  </div>
</main>

<ModalPDF 
  isOpen={showModal} 
  pdfUrl={pdfUrl} 
  titulo="Vista Previa - Registro de Matrícula"
  onClose={closeModal} 
/>
