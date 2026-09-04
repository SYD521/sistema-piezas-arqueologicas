"""Esquemas Pydantic v2 para validación y serialización de Piezas Arqueológicas."""
from datetime import date, datetime
from decimal import Decimal
from enum import Enum
from typing import Optional

from pydantic import BaseModel, ConfigDict, Field


class EstadoConservacion(str, Enum):
    """Enumeración para el estado de conservación de la pieza."""
    EXCELENTE = "EXCELENTE"
    REGULAR = "REGULAR"
    FRAGMENTADO = "FRAGMENTADO"


class PiezaBase(BaseModel):
    """Esquema base con atributos comunes para las piezas arqueológicas."""
    nombre_tipo_objeto: str = Field(
        ...,
        min_length=2,
        max_length=150,
        description="Nombre o clasificación del objeto (ej. Vasija ceremonial, Hacha de obsidiana)",
        examples=["Vasija ceremonial de barro"]
    )
    sitio_hallazgo: str = Field(
        ...,
        min_length=2,
        max_length=200,
        description="Nombre del sitio arqueológico o ubicación geográfica",
        examples=["Joya de Cerén, Estructura 3"]
    )
    latitud: Optional[Decimal] = Field(
        default=None,
        ge=-90.0,
        le=90.0,
        description="Coordenada de latitud en grados decimales (-90 a 90)",
        examples=[13.82670000]
    )
    longitud: Optional[Decimal] = Field(
        default=None,
        ge=-180.0,
        le=180.0,
        description="Coordenada de longitud en grados decimales (-180 a 180)",
        examples=[-89.35640000]
    )
    fecha_hallazgo: date = Field(
        ...,
        description="Fecha en la que fue descubierta la pieza",
        examples=["2024-02-15"]
    )
    descripcion: Optional[str] = Field(
        default=None,
        description="Descripción detallada de la pieza, morfología y contexto de campo",
        examples=["Vasija con motivos policromados bien conservados."]
    )
    estado_conservacion: EstadoConservacion = Field(
        default=EstadoConservacion.REGULAR,
        description="Estado de conservación actual del vestigio",
        examples=[EstadoConservacion.EXCELENTE]
    )


class PiezaCreate(PiezaBase):
    """Esquema para la creación de una nueva pieza arqueológica."""
    pass


class PiezaUpdate(BaseModel):
    """Esquema para la actualización parcial o total de una pieza arqueológica."""
    nombre_tipo_objeto: Optional[str] = Field(
        default=None,
        min_length=2,
        max_length=150,
        description="Nombre o clasificación del objeto"
    )
    sitio_hallazgo: Optional[str] = Field(
        default=None,
        min_length=2,
        max_length=200,
        description="Nombre del sitio arqueológico o ubicación"
    )
    latitud: Optional[Decimal] = Field(
        default=None,
        ge=-90.0,
        le=90.0,
        description="Coordenada de latitud"
    )
    longitud: Optional[Decimal] = Field(
        default=None,
        ge=-180.0,
        le=180.0,
        description="Coordenada de longitud"
    )
    fecha_hallazgo: Optional[date] = Field(
        default=None,
        description="Fecha del hallazgo"
    )
    descripcion: Optional[str] = Field(
        default=None,
        description="Descripción del objeto o contexto"
    )
    estado_conservacion: Optional[EstadoConservacion] = Field(
        default=None,
        description="Estado de conservación"
    )


class PiezaResponse(PiezaBase):
    """Esquema de respuesta serializado para el cliente."""
    id: int = Field(..., description="Identificador único autoincremental de la pieza")
    fecha_registro: datetime = Field(..., description="Fecha y hora de registro en el sistema")

    model_config = ConfigDict(from_attributes=True)
