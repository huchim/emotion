<?php namespace Emotion\Exceptions;

abstract class ExceptionCodes {
    const S_CLAIM_EMPTY = "La credencial esta vacía.";
    const E_CLAIM_EMPTY = 8;
    const S_CLAIM_WRITE_ERROR = "Los atributos de la credencial no pueden ser guardados.";
    const E_CLAIM_WRITE_ERROR = 9;
    const S_CLAIM_LIST_ERROR = "Las credenciales tienen un error.";
    const E_CLAIM_LIST_ERROR = 9;
    const S_CLAIM_REQUIRED = "Se requiere un atributo de la credencial que no fue proporcionado.";
    const E_CLAIM_REQUIRED = 10;
    const S_CLAIM_INVALID = "El atributo contiene un valor que no es válido.";
    const E_CLAIM_INVALID = 11;
    const S_CLAIM_PASSWORD = "El atributo de contraseña no está permitido.";
    const E_CLAIM_PASSWORD = 12;

    /* Core */
    const S_CONNECTIONS_EMPTY = "No existe una lista de cadenas de conexión disponible.";
    const E_CONNECTIONS_EMPTY = 13;
    const S_CONNECTIONS_MISSING = "No existe la cadena de conexión con el nombre proporcionado.";
    const E_CONNECTIONS_MISSING = 16;

    /* JSON Files */
    const S_JSON_FILE_MISSING = "El archivo JSON especificado no existe en la ruta.";
    const E_JSON_FILE_MISSING = 14;

    const S_JSON_READ_FAILED = "El archivo JSON no se pudo leer correctamente.";
    const E_JSON_READ_FAILED = 15;
}