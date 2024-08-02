#!/bin/sh

# Este script corre _croneado_ todos los días a las 23:59hs, para reservar las
# mediciones del día en otro archivo.

raiz_del_proyecto=$HOME/tanque_esp

fecha=$(date +%F)

cd $raiz_del_proyecto/cliente/
grep "$fecha" mediciones.csv >> mediciones$fecha.csv
cp mediciones$fecha.csv mediciones.csv
