"""Aplicación principal de FastAPI para el Sistema de Piezas Arqueológicas."""
from contextlib import asynccontextmanager
from typing import AsyncGenerator

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.api.piezas import router as piezas_router
from app.core.database import Base, engine


@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncGenerator[None, None]:
    """Ciclo de vida de la aplicación: asegura la creación de tablas si no existen."""
    # Asegura que las tablas definidas en los modelos existan en la BD
    try:
        Base.metadata.create_all(bind=engine)
    except Exception as exc:
        print(f"[ADVERTENCIA] No se pudo verificar/crear tablas en inicio: {exc}")
    yield


app = FastAPI(
    title="Sistema de Piezas Arqueológicas API",
    description=(
        "API REST para la Dirección Nacional de Patrimonio Cultural. "
        "Permite a los arqueólogos registrar, consultar, editar y eliminar piezas "
        "halladas durante exploraciones en campo."
    ),
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
    lifespan=lifespan,
)

# Configuración de CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Registro de routers
app.include_router(piezas_router)


@app.get("/health", tags=["Health"])
def health_check() -> dict[str, str]:
    """Endpoint de verificación de estado y salud del servicio."""
    return {
        "status": "ok",
        "service": "sistema-piezas-arqueologicas-api",
        "docs": "/docs",
    }
