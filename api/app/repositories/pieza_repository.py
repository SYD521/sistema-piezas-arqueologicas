"""Repositorio para la entidad Pieza (acceso a base de datos con SQLAlchemy 2.0)."""
from typing import Any, Optional
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.models.pieza import Pieza


class PiezaRepository:
    """Capa de acceso a datos para operaciones CRUD de piezas arqueológicas."""

    def __init__(self, db: Session) -> None:
        self.db: Session = db

    def create(self, pieza: Pieza) -> Pieza:
        """Persiste una nueva pieza arqueológica en la base de datos."""
        self.db.add(pieza)
        self.db.commit()
        self.db.refresh(pieza)
        return pieza

    def get_all(self, skip: int = 0, limit: int = 100) -> list[Pieza]:
        """Obtiene el listado de piezas arqueológicas con paginación."""
        stmt = select(Pieza).offset(skip).limit(limit).order_by(Pieza.id.desc())
        return list(self.db.scalars(stmt).all())

    def get_by_id(self, pieza_id: int) -> Optional[Pieza]:
        """Busca y retorna una pieza arqueológica por su clave primaria."""
        stmt = select(Pieza).where(Pieza.id == pieza_id)
        return self.db.scalars(stmt).first()

    def update(self, pieza: Pieza, update_data: dict[str, Any]) -> Pieza:
        """Actualiza los atributos de una pieza arqueológica existente."""
        for field, value in update_data.items():
            setattr(pieza, field, value)
        self.db.commit()
        self.db.refresh(pieza)
        return pieza

    def delete(self, pieza: Pieza) -> None:
        """Elimina físicamente una pieza arqueológica de la base de datos."""
        self.db.delete(pieza)
        self.db.commit()
