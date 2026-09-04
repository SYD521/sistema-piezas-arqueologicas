"""Configuración de conexión y sesión de Base de Datos con SQLAlchemy 2.0."""
import os
from typing import Generator
from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase, Session, sessionmaker

# Obtener variables de conexión desde el entorno
DB_USER = os.getenv("DB_USER", "arqueologo_user")
DB_PASSWORD = os.getenv("DB_PASSWORD", "secret_db_password")
DB_HOST = os.getenv("DB_HOST", "db")
DB_PORT = os.getenv("DB_PORT", "3306")
DB_NAME = os.getenv("DB_NAME", "patrimonio_db")

# Cadena de conexión con driver pymysql
DATABASE_URL = (
    f"mysql+pymysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}?charset=utf8mb4"
)

# Creación del motor de base de datos con verificación previa de conexión activa
engine = create_engine(
    DATABASE_URL,
    echo=False,
    pool_pre_ping=True,
    pool_recycle=3600,
)

# Fábrica de sesiones de base de datos
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)


class Base(DeclarativeBase):
    """Clase base declarativa para los modelos ORM de SQLAlchemy 2.0."""
    pass


def get_db() -> Generator[Session, None, None]:
    """Generador de dependencias de FastAPI para inyectar la sesión de BD en cada request."""
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
