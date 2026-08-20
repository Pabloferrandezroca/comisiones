<?php
/**
 * This file is part of Comisiones plugin for FacturaScripts.
 * Copyright (C) 2026 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Comisiones\Extension\Controller;

use Closure;
use FacturaScripts\Core\DbQuery;
use FacturaScripts\Core\Where;
use FacturaScripts\Dinamic\Model\LiquidacionComision;

/**
 * Description of Dashboard
 *
 * @author Daniel Fernández Giménez <contacto@danielfg.es>
 */
class Dashboard
{
    /**
     * Devuelve el total de comisiones (campo totalcomision de facturascli) del mes
     * actual y de los 3 meses anteriores para el agente vinculado al usuario.
     * Si el usuario no tiene agente vinculado devuelve un array vacío.
     *
     * @return Closure
     */
    public function getComisiones(): Closure
    {
        return function () {
            if (empty($this->user->codagente)) {
                return [];
            }

            $meses = [];
            for ($i = 0; $i < 4; $i++) {
                $desde = date('Y-m-01', strtotime('-' . $i . ' month'));
                $hasta = date('Y-m-t', strtotime('-' . $i . ' month'));

                $total = DbQuery::table('facturascli')
                    ->whereEq('codagente', $this->user->codagente)
                    ->whereGte('fecha', $desde)
                    ->whereLte('fecha', $hasta)
                    ->sum('totalcomision');

                $meses[] = [
                    'label' => date('m/Y', strtotime($desde)),
                    'total' => $total,
                ];
            }

            return $meses;
        };
    }

    /**
     * Devuelve las 5 últimas liquidaciones de comisión del agente vinculado
     * al usuario. Si el usuario no tiene agente vinculado devuelve un array vacío.
     *
     * @return Closure
     */
    public function getLiquidaciones(): Closure
    {
        return function () {
            if (empty($this->user->codagente)) {
                return [];
            }

            $where = [Where::eq('codagente', $this->user->codagente)];

            return LiquidacionComision::all($where, ['fecha' => 'DESC'], 0, 5);
        };
    }
}
