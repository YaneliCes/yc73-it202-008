<?php
/* yc73 4/1/23 */
function reset_session()
{
    session_unset();
    session_destroy();
    session_start();
}