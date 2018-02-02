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

    /* router */
    const S_ROUTER_INVALID = "El enrutador no ha sido inicializado o no existe.";
    const E_ROUTER_INVALID = 17;
    const S_ROUTER_NOT_FOUND = "Ninguna ruta pudo entender la solicitud.";
    const E_ROUTER_NOT_FOUND = 20;

    /* response */
    const S_RESPONSE_ERROR = "Ocurrió un problema al intentar procesar la solicitud";
    const E_RESPONSE_ERROR = 18;
    const S_RESPONSE_HEADER_ERROR = "No se puede enviar el encabezado \"%s\".";
    const E_RESPONSE_HEADER_ERROR = 22;

    /** controller */
    const S_CONTROLLER_CLASS_NOT_FOUND = "No se pudo localizar el archivo que contiene el controlador \"%s\" en la carpeta \"%s\".";
    const E_CONTROLLER_CLASS_NOT_FOUND = 19;

    /** files */
    const S_ROUTE_STATIC_FILE_NOTFOUND = "No se pudo localizar el archivo \"%s\" en la carpeta \"%s\".";
    const E_ROUTE_STATIC_FILE_NOTFOUND = 21;

    
}