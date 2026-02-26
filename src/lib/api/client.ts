import type { Estudiante, ApiError } from '../types/student';

const API_BASE_URL = import.meta.env.VITE_API_URL || '';

export class ApiClient {
  private baseUrl: string;

  constructor(baseUrl: string = API_BASE_URL) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
  }

  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`;
    
    const response = await fetch(url, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
    });

    if (!response.ok) {
      const error: unknown = await response.json().catch(() => ({ error: response.statusText }));
      throw error;
    }

    return response.json();
  }

  async buscarEstudiante(criterio: string): Promise<Estudiante[]> {
    const params = new URLSearchParams({ criterio });
    return this.request<Estudiante[]>(`/api/buscar.php?${params}`);
  }

  getPdfUrl(codigo: string): string {
    return `${this.baseUrl}/api/pdf.php?codigo=${encodeURIComponent(codigo)}`;
  }

  descargarPdf(codigo: string): void {
    const url = this.getPdfUrl(codigo);
    window.open(url, '_blank');
  }
}

export const apiClient = new ApiClient();
