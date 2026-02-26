<script lang="ts">
  let { 
    isOpen = false, 
    pdfUrl = '',
    titulo = 'Vista Previa del PDF',
    onClose 
  }: { 
    isOpen: boolean;
    pdfUrl: string;
    titulo?: string;
    onClose: () => void;
  } = $props();

  function handleBackdropClick(e: MouseEvent) {
    if (e.target === e.currentTarget) {
      onClose();
    }
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
      onClose();
    }
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if isOpen && pdfUrl}
  <div 
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
    onclick={handleBackdropClick}
    role="dialog"
    aria-modal="true"
  >
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-5xl h-[90vh] flex flex-col">
      <!-- Header del modal -->
      <div class="flex items-center justify-between px-4 py-3 border-b bg-gray-50 rounded-t-lg">
        <h3 class="text-lg font-semibold text-gray-800">{titulo}</h3>
        <button
          onclick={onClose}
          class="p-1 hover:bg-gray-200 rounded-full transition-colors"
          aria-label="Cerrar"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- Contenido del PDF -->
      <div class="flex-1 overflow-hidden">
        <iframe
          src={pdfUrl}
          title="Vista Previa PDF"
          class="w-full h-full border-0"
        ></iframe>
      </div>
      
      <!-- Footer con botones -->
      <div class="flex items-center justify-end gap-3 px-4 py-3 border-t bg-gray-50 rounded-b-lg">
        <button
          onclick={onClose}
          class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors"
        >
          Cerrar
        </button>
        <a
          href={pdfUrl}
          download
          class="px-4 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Descargar PDF
        </a>
      </div>
    </div>
  </div>
{/if}
