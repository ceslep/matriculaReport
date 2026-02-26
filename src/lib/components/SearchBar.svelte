<script lang="ts">
  let { 
    onSearch, 
    loading = false 
  }: { 
    onSearch: (criterio: string) => void; 
    loading?: boolean;
  } = $props();

  let searchValue = $state('');

  function handleSubmit(e: Event) {
    e.preventDefault();
    if (searchValue.trim()) {
      onSearch(searchValue.trim());
    }
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') {
      handleSubmit(e);
    }
  }
</script>

<form onsubmit={handleSubmit} class="flex gap-2">
  <input
    type="search"
    bind:value={searchValue}
    onkeydown={handleKeydown}
    placeholder="Ingrese código, estudiante o nombre"
    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
  />
  <button
    type="submit"
    disabled={loading || !searchValue.trim()}
    class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
  >
    {#if loading}
      <span class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
    {/if}
    Buscar
  </button>
</form>
