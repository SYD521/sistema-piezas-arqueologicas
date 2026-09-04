"""Capa de servicios con la lógica de negocio para Piezas Arqueológicas."""
from datetime import date
from typing import Any
from fastapi import HTTPException, status
from sqlalchemy.orm import Session

from app.models.pieza import Pieza
from app.repositories.pieza_repository import PiezaRepository
from app.schemas.pieza import PiezaCreate, PiezaUpdate


class PiezaService:
    """Servicio que encapsula las reglas de negocio y orquesta el repositorio."""

    def __init__(self, db: Session) -> None:
        self.repository = PiezaRepository(db)

    def _validar_reglas_negocio(
        self,
        fecha_hallazgo: date | None,
        latitud: Any | None,
        longitud: Any | None,
    ) -> None:
        """Valida coherencia de fechas y coordenadas geográficas."""
        if fecha_hallazgo and fecha_hallazgo > date.today():
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="La fecha de hallazgo no puede ser posterior a la fecha actual.",
            )

        # Si se especifica una coordenada, la otra también debe estar presente
        if (latitud is not None and longitud is None) or (latitud is None and longitud is not None):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Debe proporcionar ambas coordenadas geográficas (latitud y longitud) o ninguna.",
            )

    def create_pieza(self, pieza_in: PiezaCreate) -> Pieza:
        """Crea una nueva pieza arqueológica validando reglas de negocio."""
        self._validar_reglas_negocio(
            fecha_hallazgo=pieza_in.fecha_hallazgo,
            latitud=pieza_in.latitud,
            longitud=pieza_in.longitud,
        )

        # Mapear del esquema Pydantic al modelo de dominio SQLAlchemy
        pieza_data = pieza_in.model_dump()
        # Convertir enum a su valor string si aplica
        if hasattr(pieza_in.estado_conservacion, "value"):
            pieza_data["estado_conservacion"] = pieza_in.estado_conservacion.value

        pieza = Pieza(**pieza_data)
        return self.repository.create(pieza)

    def list_piezas(self, skip: int = 0, limit: int = 100) -> list[Pieza]:
        """Obtiene la lista de piezas arqueológicas registradas."""
        return self.repository.get_all(skip=skip, limit=limit)

    def get_pieza_by_id(self, pieza_id: int) -> Pieza:
        """Obtiene una pieza por ID o levanta HTTP 404 si no existe."""
        pieza = self.repository.get_by_id(pieza_id)
        if not pieza:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail=f"Pieza arqueológica con ID {pieza_id} no fue encontrada.",
            )
        return pieza

    def update_pieza(self, pieza_id: int, pieza_in: PiezaUpdate) -> Pieza:
        """Actualiza los datos de una pieza existente."""
        pieza = self.get_pieza_by_id(pieza_id)

        update_data = pieza_in.model_dump(exclude_unset=True)
        if not update_data:
            return pieza

        # Extraer coordenadas y fecha proyectadas para validación
        nueva_fecha = update_data.get("fecha_hallazgo", pieza.fecha_hallazgo)
        nueva_lat = update_data.get("latitud", pieza.latitud)
        nueva_lon = update_data.get("longitud", pieza.longitud)

        self._validar_reglas_negocio(
            fecha_hallazgo=nueva_fecha,
            latitud=nueva_lat,
            longitud=nueva_lon,
        )

        # Convertir enum si se proporcionó
        if "estado_conservacion" in update_data and hasattr(
            update_data["estado_conservacion"], "value"
        ):
            update_data["estado_conservacion"] = update_data["estado_conservacion"].value

        return self.repository.update(pieza, update_data)

    def delete_pieza(self, pieza_id: int) -> None:
        """Elimina una pieza existente o levanta HTTP 404 si no existe."""
        pieza = self.get_pieza_by_id(pieza_id)
        self.repository.delete(pieza)
