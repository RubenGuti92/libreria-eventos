<?php declare(strict_types=1);


namespace SincronizarEvento;


use Exception;

abstract class AbstractSincronizar implements Sincronizar
{
    /**
     * Función que genera el id del evento sirve para generarlo como para poder identificar el evento
     *
     * @param Evento $evento
     * @param string $calendario
     * @return string
     */
    public static function generarIdEvento(Evento $evento, Calendario $calendario): string
    {
        //Nos quedamos con la parte del usuario quitando la parte del correo
        $calendario = explode('@', $calendario->getCalendario());
        $calendario = $calendario[0];
        $calendario = str_replace('.', '', $calendario);

        //Generamos una cadena que sera el id unico del evento une usuario-idEvento-año del evento
        $id = self::PREFIJO_ID_EVENTO . '-' . $calendario . '-' . $evento->getIdEvento() . '-' . date('Y', strtotime($evento->getFechaInicio()));

        //Ciframos en hexadecimal para no tener problemas con simbolos extraños
        $id = bin2hex($id);
        return $id;
    }

    /**
     * Función que genera el id del evento sirve para generarlo como para poder identificar el evento
     *
     * @param string $idUnicoEvento
     * @return array|false
     */
    protected static function descifrarIdEvento(string $idUnicoEvento)
    {
        //Array que devolveremos con los datos
        $descomposicion_id_evento = array();

        //Comprobamos si el valor pasado es hexadecimal para ver si lo podemos descifrar
        if (ctype_xdigit($idUnicoEvento)) {
            // Pasamos a string el hexadecimal que hemos recibido y que ha sido cifrado por la funcion generarIdEvento
            // comprobando si es un evento generado por nosotros, si no es asi lo "descartamos"
            $idUnicoEvento = hex2bin($idUnicoEvento);
            $partes_id = explode('-', $idUnicoEvento);
            if (isset($partes_id[0]) && $partes_id[0] == self::PREFIJO_ID_EVENTO) {
                $descomposicion_id_evento['calendario'] = $partes_id[1];
                $descomposicion_id_evento['idEvento'] = $partes_id[2];
                $descomposicion_id_evento['anioEvento'] = $partes_id[3];
            } else {
                $descomposicion_id_evento = false;
            }
        } else {
            $descomposicion_id_evento = false;
        }

        return $descomposicion_id_evento;
    }
}