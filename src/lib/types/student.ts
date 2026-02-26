export interface Estudiante {
  codigo: string;
  year: number;
  estudiante: string;
  tipoSangre: string;
  nombres: string;
  genero: string;
  email_estudiante: string;
  sede: string;
  fecnac: string;
  edad: number;
  lugarNacimiento: string;
  tdei: string;
  fechaExpedicion: string;
  lugarExpedicion: string;
  telefono1: string;
  telefono2: string;
  direccion: string;
  lugar: string;
  sisben: string;
  estrato: string;
  eps: string;
  activo: string;
  banda: string;
  desertor: string;
  eanterior: string;
  estado: string;
  asignacion: string;
  nivel: string;
  numero: string;
  institucion_externa: string;
  otraInformacion: string;
  padre: string;
  padreid: string;
  ocupacionpadre: string;
  telefonopadre: string;
  madre: string;
  madreid: string;
  ocupacionmadre: string;
  telefonomadre: string;
  acudiente: string;
  idacudiente: string;
  parentesco: string;
  telefono_acudiente: string;
  victimaConflicto: string;
  lugarDesplazamiento: string;
  fechaDesplazamiento: string;
  HED: string;
  etnia: string;
  discapacidad: string;
}

export interface ApiError {
  error: string;
}
