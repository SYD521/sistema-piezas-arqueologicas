"""Controlador / Router para endpoints REST de Piezas Arqueológicas."""
from typing import Any
from fastapi import APIRouter, Depends, Query, status
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.schemas.pieza import PiezaCreate, PiezaResponse, PiezaUpdate
from app.services.pieza_service import PiezaService

router = APIRouter(
    prefix="/piezas",
    tags=["Piezas Arqueológicas"],
)


def get_pieza_service(db: Session = Depends(get_db)) -> PiezaService:
    """Inyección de dependencias para el servicio de piezas."""
    return PiezaService(db)


@router.post(
    "",
    response_model=PiezaResponse,
    status_code=status.HTTP_201_CREATED,
    summary="Registrar una nueva pieza arqueológica",
    description="Crea una nueva pieza arqueológica en la base de datos validando sus datos y reglas de negocio.",
)
def create_pieza(
    pieza_in: PiezaCreate,
    service: PiezaService = Depends(get_pieza_service),
) -> Any:
    """Endpoint para registrar un nuevo hallazgo arqueológico."""
    return service.create_pieza(pieza_in)


@router.get(
    "",
    response_model=list[PiezaResponse],
    status_code=status.HTTP_200_OK,
    summary="Listar piezas arqueológicas registradas",
    description="Retorna una lista paginada de todas las piezas arqueológicas encontradas.",
)
def list_piezas(
    skip: int = Query(default=0, ge=0, description="Número de registros a omitir"),
    limit: int = Query(default=100, ge=1, le=500, description="Cantidad máxima de registros a retornar"),
    service: PiezaService = Depends(get_pieza_service),
) -> Any:
    """Endpoint para listar piezas arqueológicas con paginación."""
    return service.list_piezas(skip=skip, limit=limit)


@router.get(
    "/{pieza_id}",
    response_model=PiezaResponse,
    status_code=status.HTTP_200_OK,
    summary="Obtener una pieza arqueológica por ID",
    description="Retorna los detalles completos de una pieza arqueológica específica.",
)
def get_pieza(
    pieza_id: int,
    service: PiezaService = Depends(get_pieza_service),
) -> Any:
    """Endpoint para consultar una pieza por su identificador único."""
    return service.get_pieza_by_id(pieza_id)


@router.put(
    "/{pieza_id}",
    response_model=PiezaResponse,
    status_code=status.HTTP_200_OK,
    summary="Actualizar una pieza arqueológica existente",
    description="Modifica los atributos (como estado de conservación o descripción) de una pieza existente.",
)
def update_pieza(
    pieza_id: int,
    pieza_in: PiezaUpdate,
    service: PiezaService = Depends(get_pieza_service),
) -> Any:
    """Endpoint para modificar una pieza arqueológica existente."""
    return service.update_pieza(pieza_id, pieza_in)


@router.delete(
    "/{pieza_id}",
    status_code=status.HTTP_200_OK,
    summary="Eliminar una pieza arqueológica",
    description="Elimina definitivamente una pieza arqueológica por su identificador único.",
)
def delete_pieza(
    pieza_id: int,
    service: PiezaService = Depends(get_pieza_service),
) -> dict[str, Any]:
    """Endpoint para eliminar un registro de pieza arqueológica."""
    service.delete_pieza(pieza_id)
    return {
        "message": "Pieza arqueológica eliminada exitosamente",
        "id": pieza_id,
    }
