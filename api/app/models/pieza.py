"""Modelo ORM para la tabla piezas_arqueologicas."""
from datetime import date, datetime
from decimal import Decimal
from typing import Optional

from sqlalchemy import Date, DateTime, Enum, Integer, Numeric, String, Text, func
from sqlalchemy.orm import Mapped, mapped_column

from app.core.database import Base


class Pieza(Base):
    """Entidad que mapea la tabla piezas_arqueologicas en MySQL."""

    __tablename__ = "piezas_arqueologicas"

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True, index=True)
    nombre_tipo_objeto: Mapped[str] = mapped_column(String(150), nullable=False)
    sitio_hallazgo: Mapped[str] = mapped_column(String(200), nullable=False, index=True)
    latitud: Mapped[Optional[Decimal]] = mapped_column(Numeric(10, 8), nullable=True)
    longitud: Mapped[Optional[Decimal]] = mapped_column(Numeric(11, 8), nullable=True)
    fecha_hallazgo: Mapped[date] = mapped_column(Date, nullable=False, index=True)
    descripcion: Mapped[Optional[str]] = mapped_column(Text, nullable=True)
    estado_conservacion: Mapped[str] = mapped_column(
        Enum("EXCELENTE", "REGULAR", "FRAGMENTADO", name="estado_conservacion_enum"),
        nullable=False,
        default="REGULAR",
    )
    fecha_registro: Mapped[datetime] = mapped_column(
        DateTime, nullable=False, server_default=func.now()
    )

    def __repr__(self) -> str:
        return f"<Pieza(id={self.id}, nombre='{self.nombre_tipo_objeto}', sitio='{self.sitio_hallazgo}')>"
