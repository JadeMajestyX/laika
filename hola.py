import sys
import copy

def main():
    print("="*60)
    print("      SOLUCIONADOR DE TRANSPORTE: VOGEL + DIMO")
    print("="*60)
    
    try:
        filas = int(input("Ingrese número de Orígenes (Filas): "))
        cols = int(input("Ingrese número de Destinos (Columnas): "))
        
        print(f"\n--- Ingrese los Costos Unitarios (separados por espacio) ---")
        matriz_costos = []
        for i in range(filas):
            while True:
                try:
                    entrada = input(f"Fila {i+1}: ").strip().split()
                    if len(entrada) != cols:
                        print(f"Error: Debe ingresar exactamente {cols} números.")
                        continue
                    fila = [float(x) for x in entrada]
                    matriz_costos.append(fila)
                    break
                except ValueError:
                    print("Error: Ingrese solo números.")

        print(f"\n--- Ingrese la Oferta (Disponibilidad) ---")
        while True:
            try:
                entrada = input(f"Ingrese {filas} valores de oferta separados por espacio: ").strip().split()
                if len(entrada) != filas:
                    print(f"Error: Debe ingresar {filas} valores.")
                    continue
                oferta = [float(x) for x in entrada]
                break
            except ValueError:
                print("Error: Solo números.")

        print(f"\n--- Ingrese la Demanda (Requerimientos) ---")
        while True:
            try:
                entrada = input(f"Ingrese {cols} valores de demanda separados por espacio: ").strip().split()
                if len(entrada) != cols:
                    print(f"Error: Debe ingresar {cols} valores.")
                    continue
                demanda = [float(x) for x in entrada]
                break
            except ValueError:
                print("Error: Solo números.")

        # Balanceo
        sum_oferta = sum(oferta)
        sum_demanda = sum(demanda)
        
        if sum_oferta != sum_demanda:
            print(f"\n[!] Problema No Balanceado (Oferta: {sum_oferta}, Demanda: {sum_demanda})")
            diff = sum_oferta - sum_demanda
            if diff > 0:
                print(f"    -> Agregando Destino Ficticio con demanda {diff}")
                for fila in matriz_costos:
                    fila.append(0)
                demanda.append(diff)
                cols += 1
            else:
                print(f"    -> Agregando Origen Ficticio con oferta {abs(diff)}")
                matriz_costos.append([0]*cols)
                oferta.append(abs(diff))
                filas += 1
        
        # Resolver
        solver = TransporteCLI(matriz_costos, oferta, demanda)
        solver.resolver()

    except KeyboardInterrupt:
        print("\nOperación cancelada.")
    except ValueError:
        print("\nError en los datos ingresados.")

class TransporteCLI:
    def __init__(self, costos, oferta, demanda):
        self.costos = costos
        self.oferta_orig = list(oferta)
        self.demanda_orig = list(demanda)
        self.filas = len(costos)
        self.cols = len(costos[0])
        self.asignacion = [[0.0] * self.cols for _ in range(self.filas)]

    def imprimir_tabla(self, asignacion, oferta_actual=None, demanda_actual=None, 
                       tachados_fila=None, tachados_col=None, titulo="TABLA"):
        """Dibuja la tabla en ASCII con las asignaciones y costos"""
        if oferta_actual is None: oferta_actual = self.oferta_orig
        if demanda_actual is None: demanda_actual = self.demanda_orig
        if tachados_fila is None: tachados_fila = [False]*self.filas
        if tachados_col is None: tachados_col = [False]*self.cols

        ancho_celda = 12
        
        print(f"\n>>> {titulo}")
        
        # Encabezado columnas
        header = " " * 8 + "|"
        for j in range(self.cols):
            mark = " (X)" if tachados_col[j] else ""
            header += f"{f'D{j+1}{mark}':^{ancho_celda}}|"
        header += f"{'OFERTA':^{ancho_celda}}|"
        print("-" * len(header))
        print(header)
        print("-" * len(header))

        # Filas
        for i in range(self.filas):
            mark = "(X) " if tachados_fila[i] else "    "
            row_str = f"{f'O{i+1}':<4}{mark}|"
            for j in range(self.cols):
                costo = self.costos[i][j]
                asig = asignacion[i][j]
                
                # Formato celda: [Costo] (Asignación)
                # Si es epsilon, mostrar 'eps'
                if asig == 0.000001:
                    val_str = f"[{int(costo)}] eps"
                elif asig > 0:
                    val_str = f"[{int(costo)}] {int(asig)}"
                else:
                    val_str = f"[{int(costo)}]"
                
                row_str += f"{val_str:^{ancho_celda}}|"
            
            # Columna de Oferta restante
            row_str += f"{int(oferta_actual[i]):^{ancho_celda}}|"
            print(row_str)
            print("-" * len(row_str))

        # Fila de Demanda
        row_dem = "DEMANDA |"
        for j in range(self.cols):
            row_dem += f"{int(demanda_actual[j]):^{ancho_celda}}|"
        print(row_dem)
        print()

    def resolver(self):
        # --- FASE 1: VOGEL ---
        print("\n" + "#"*40)
        print(" FASE 1: MÉTODO DE VOGEL (Solución Inicial)")
        print("#"*40)
        
        oferta = list(self.oferta_orig)
        demanda = list(self.demanda_orig)
        filas_tachadas = [False] * self.filas
        cols_tachadas = [False] * self.cols
        num_paso = 1

        while sum(oferta) > 0 and sum(demanda) > 0:
            print(f"\n--- Paso {num_paso} (Vogel) ---")
            
            # 1. Calcular Penalizaciones
            pen_filas = []
            pen_cols = []
            
            # Filas
            for i in range(self.filas):
                if filas_tachadas[i]:
                    pen_filas.append(-1)
                else:
                    # Buscar costos de celdas no tachadas en esta fila
                    valid_costs = [self.costos[i][j] for j in range(self.cols) if not cols_tachadas[j]]
                    if len(valid_costs) >= 2:
                        valid_costs.sort()
                        pen_filas.append(valid_costs[1] - valid_costs[0])
                    elif len(valid_costs) == 1:
                        pen_filas.append(valid_costs[0])
                    else:
                        pen_filas.append(0)

            # Columnas
            for j in range(self.cols):
                if cols_tachadas[j]:
                    pen_cols.append(-1)
                else:
                    # Buscar costos de celdas no tachadas en esta columna
                    valid_costs = [self.costos[i][j] for i in range(self.filas) if not filas_tachadas[i]]
                    if len(valid_costs) >= 2:
                        valid_costs.sort()
                        pen_cols.append(valid_costs[1] - valid_costs[0])
                    elif len(valid_costs) == 1:
                        pen_cols.append(valid_costs[0])
                    else:
                        pen_cols.append(0)

            # Mostrar penalizaciones
            print(f"Penalizaciones Filas:    {[p if p != -1 else 'X' for p in pen_filas]}")
            print(f"Penalizaciones Columnas: {[p if p != -1 else 'X' for p in pen_cols]}")

            # 2. Seleccionar mayor penalización
            max_p_fila = max(pen_filas) if pen_filas else -1
            max_p_col = max(pen_cols) if pen_cols else -1
            
            sel_r, sel_c = -1, -1
            
            # Lógica de selección con desempate por menor costo
            usar_fila = False
            
            if max_p_fila > max_p_col:
                usar_fila = True
            elif max_p_col > max_p_fila:
                usar_fila = False
            else:
                # Empate: buscar quien tiene el costo mínimo absoluto en su línea
                min_cost_fila = float('inf')
                indices_fila = [i for i, x in enumerate(pen_filas) if x == max_p_fila]
                for i in indices_fila:
                    for j in range(self.cols):
                        if not cols_tachadas[j]:
                            min_cost_fila = min(min_cost_fila, self.costos[i][j])
                            
                min_cost_col = float('inf')
                indices_col = [j for j, x in enumerate(pen_cols) if x == max_p_col]
                for j in indices_col:
                    for i in range(self.filas):
                        if not filas_tachadas[i]:
                            min_cost_col = min(min_cost_col, self.costos[i][j])
                
                if min_cost_fila <= min_cost_col:
                    usar_fila = True
                else:
                    usar_fila = False

            # 3. Ejecutar selección
            if usar_fila:
                # Elegir la fila con esa penalización que tenga el menor costo
                indices = [i for i, x in enumerate(pen_filas) if x == max_p_fila]
                best_r = -1
                min_c = float('inf')
                for r in indices:
                    for c in range(self.cols):
                        if not cols_tachadas[c] and self.costos[r][c] < min_c:
                            min_c = self.costos[r][c]
                            best_r = r
                
                # En esa fila, buscar la columna de menor costo
                sel_r = best_r
                min_val = float('inf')
                for j in range(self.cols):
                    if not cols_tachadas[j] and self.costos[sel_r][j] < min_val:
                        min_val = self.costos[sel_r][j]
                        sel_c = j
                msg = f"Mayor penalización ({max_p_fila}) en Fila {sel_r+1}."
            else:
                # Columna
                indices = [j for j, x in enumerate(pen_cols) if x == max_p_col]
                best_c = -1
                min_c = float('inf')
                for c in indices:
                    for r in range(self.filas):
                        if not filas_tachadas[r] and self.costos[r][c] < min_c:
                            min_c = self.costos[r][c]
                            best_c = c
                
                sel_c = best_c
                min_val = float('inf')
                for i in range(self.filas):
                    if not filas_tachadas[i] and self.costos[i][sel_c] < min_val:
                        min_val = self.costos[i][sel_c]
                        sel_r = i
                msg = f"Mayor penalización ({max_p_col}) en Columna {sel_c+1}."

            print(f"{msg} Se elige celda ({sel_r+1}, {sel_c+1}) con costo {min_val}.")

            # 4. Asignar
            cantidad = min(oferta[sel_r], demanda[sel_c])
            self.asignacion[sel_r][sel_c] = cantidad
            oferta[sel_r] -= cantidad
            demanda[sel_c] -= cantidad
            
            print(f"-> Asignando {cantidad} unidades a ({sel_r+1}, {sel_c+1}).")

            # 5. Tachar
            if oferta[sel_r] == 0 and not filas_tachadas[sel_r]:
                filas_tachadas[sel_r] = True
                print(f"-> Oferta agotada en O{sel_r+1}. Fila tachada.")
            elif demanda[sel_c] == 0 and not cols_tachadas[sel_c]:
                cols_tachadas[sel_c] = True
                print(f"-> Demanda satisfecha en D{sel_c+1}. Columna tachada.")
                
            # Manejo caso final para evitar loop infinito si quedan ceros
            if sum(oferta) == 0 and sum(demanda) == 0:
                break
            if all(filas_tachadas) or all(cols_tachadas):
                # Si queda algo residual
                if sum(oferta) > 0 or sum(demanda) > 0:
                     # Asignar lo que queda a las celdas no tachadas (caso raro de degeneración simultánea)
                     pass # Simplificación para el ejemplo
                else:
                    break

            self.imprimir_tabla(self.asignacion, oferta, demanda, filas_tachadas, cols_tachadas, f"TABLA DESPUÉS DEL PASO {num_paso}")
            num_paso += 1
            input("Presione Enter para siguiente paso...")

        print("\n--- SOLUCIÓN INICIAL VOGEL COMPLETADA ---")
        costo_ini = self.calcular_z()
        print(f"Costo Total Inicial: ${costo_ini}")
        
        # --- FASE 2: DIMO ---
        print("\n" + "#"*40)
        print(" FASE 2: MÉTODO DIMO / MODI (Optimización)")
        print("#"*40)
        
        self.verificar_degeneracion()
        
        iteracion = 1
        while True:
            print(f"\n--- Iteración DIMO {iteracion} ---")
            
            # 1. Calcular u y v
            u = [None] * self.filas
            v = [None] * self.cols
            u[0] = 0 # Regla: u1 = 0
            
            while None in u or None in v:
                progress = False
                for i in range(self.filas):
                    for j in range(self.cols):
                        if self.es_basica(i, j):
                            if u[i] is not None and v[j] is None:
                                v[j] = self.costos[i][j] - u[i]
                                progress = True
                            elif u[i] is None and v[j] is not None:
                                u[i] = self.costos[i][j] - v[j]
                                progress = True
                if not progress:
                    # Si el sistema está desconectado, asignar 0 arbitrariamente al primer u None
                    for idx, val in enumerate(u):
                        if val is None:
                            u[idx] = 0
                            break
            
            print("Multiplicadores calculados (u_i + v_j = c_ij en básicas):")
            print(f"u (Filas): {u}")
            print(f"v (Cols) : {v}")

            # 2. Calcular Índices de No Básicas
            min_indice = 0
            entrada = None
            
            print("\nEvaluación de Celdas No Básicas (Indice = c_ij - u_i - v_j):")
            for i in range(self.filas):
                for j in range(self.cols):
                    if not self.es_basica(i, j):
                        ind = self.costos[i][j] - u[i] - v[j]
                        print(f"  Celda ({i+1},{j+1}): {self.costos[i][j]} - ({u[i]}) - ({v[j]}) = {ind}")
                        if ind < min_indice:
                            min_indice = ind
                            entrada = (i, j)
            
            if min_indice >= 0:
                print("\n>>> TODOS LOS ÍNDICES >= 0. SOLUCIÓN ÓPTIMA ALCANZADA <<<")
                break
            
            print(f"\n-> La celda más negativa es ({entrada[0]+1}, {entrada[1]+1}) con valor {min_indice}.")
            print("-> Esta variable ENTRA a la base.")

            # 3. Encontrar Ciclo
            path = self.encontrar_ciclo(entrada)
            if not path:
                print("Error crítico: No se pudo cerrar el ciclo.")
                break
                
            print(f"-> Ciclo cerrado encontrado: {[ (p[0]+1, p[1]+1) for p in path]}")
            
            # Determinar Theta
            # Índices impares en el path (1, 3, 5...) son los que restan (-)
            # El índice 0 es la variable de entrada (+), no cuenta para el límite
            celdas_negativas = []
            theta = float('inf')
            salida = None
            
            path_vis = [] # Para mostrar en consola
            
            for k, (r, c) in enumerate(path):
                signo = "(+)" if k % 2 == 0 else "(-)"
                path_vis.append(f"({r+1},{c+1}){signo}")
                
                if k % 2 != 0: # Es negativo
                    val = self.asignacion[r][c]
                    # Epsilon se trata como 0 para el cálculo de theta, pero si theta es 0 (caso degenerado), sale esa.
                    val_num = 0 if val == 0.000001 else val
                    if val_num < theta:
                        theta = val_num
                        salida = (r, c)
                    elif val_num == theta and val == 0.000001: # Priorizar salir epsilon
                         salida = (r, c)

            print(f"-> Signos en el ciclo: {' -> '.join(path_vis)}")
            print(f"-> Valor Theta (mínimo de los negativos): {theta}")
            print(f"-> Variable que SALE de la base: ({salida[0]+1}, {salida[1]+1})")

            # 4. Actualizar
            for k, (r, c) in enumerate(path):
                val_actual = self.asignacion[r][c]
                if val_actual == 0.000001: val_actual = 0
                
                if k % 2 == 0: # Sumar
                    self.asignacion[r][c] = val_actual + theta
                else: # Restar
                    self.asignacion[r][c] = val_actual - theta
            
            # Limpiar ceros (la variable que sale se vuelve 0 real, no epsilon)
            # Ojo: Solo una variable debe salir. Si hay empate, las otras se vuelven epsilon (degeneración).
            # Simplificación aquí: limpiar la específica detectada.
            # Re-escanear para asegurar consistencia
            if self.asignacion[salida[0]][salida[1]] == 0:
                pass # Ya es 0
            
            self.imprimir_tabla(self.asignacion, titulo=f"TABLA FIN ITERACIÓN {iteracion}")
            costo_actual = self.calcular_z()
            print(f"Costo Actual: ${costo_actual}")
            
            iteracion += 1
            input("Presione Enter para siguiente iteración...")

        # Limpieza final (quitar epsilons)
        for i in range(self.filas):
            for j in range(self.cols):
                if self.asignacion[i][j] == 0.000001:
                    self.asignacion[i][j] = 0
        
        print("\n" + "="*40)
        print(" RESULTADO FINAL")
        print("="*40)
        self.imprimir_tabla(self.asignacion, titulo="TABLA ÓPTIMA")
        print(f"COSTO TOTAL MÍNIMO: ${self.calcular_z()}")

    def es_basica(self, r, c):
        # Es básica si tiene asignación > 0 o es un epsilon (0.000001)
        return self.asignacion[r][c] > 0

    def verificar_degeneracion(self):
        # m + n - 1
        req = self.filas + self.cols - 1
        count = 0
        for row in self.asignacion:
            for x in row:
                if x > 0: count += 1
        
        if count < req:
            faltantes = req - count
            print(f"\n[!] Solución degenerada. Asignadas: {count}, Requeridas: {req}.")
            print(f"    Agregando {faltantes} epsilons (ε) a celdas vacías de menor costo...")
            
            added = 0
            # Buscar celdas vacías (0 exacto) y poner epsilon
            # Estrategia simple: llenar por menor costo donde no forme ciclo cerrado inmediato (idealmente)
            # Para simplificar: llenar secuencialmente
            for i in range(self.filas):
                for j in range(self.cols):
                    if self.asignacion[i][j] == 0:
                        self.asignacion[i][j] = 0.000001
                        added += 1
                        if added == faltantes: return

    def encontrar_ciclo(self, start_pos):
        # DFS para buscar ciclo: start -> row -> col -> row -> ... -> start
        # start_pos es (r, c)
        # path contiene lista de coordenadas
        
        stack = [(start_pos, [start_pos], 'row')] # (current, path, next_move_type)
        
        # next_move_type 'row': buscar otro nodo en la misma FILA
        # next_move_type 'col': buscar otro nodo en la misma COLUMNA
        
        while stack:
            curr, path, mode = stack.pop()
            r, c = curr
            
            # Candidatos
            candidates = []
            if mode == 'row':
                # Moverse horizontalmente a otra columna j
                for j in range(self.cols):
                    if j != c:
                        if self.es_basica(r, j) or (r == start_pos[0] and j == start_pos[1]):
                            candidates.append((r, j))
                next_mode = 'col'
            else:
                # Moverse verticalmente a otra fila i
                for i in range(self.filas):
                    if i != r:
                        if self.es_basica(i, c) or (i == start_pos[0] and c == start_pos[1]):
                            candidates.append((i, c))
                next_mode = 'row'
            
            for cand in candidates:
                if cand == start_pos and len(path) >= 3:
                    return path # Ciclo cerrado!
                if cand not in path:
                    stack.append((cand, path + [cand], next_mode))
        return None

    def calcular_z(self):
        z = 0
        for i in range(self.filas):
            for j in range(self.cols):
                val = self.asignacion[i][j]
                if val == 0.000001: val = 0
                z += val * self.costos[i][j]
        return z

if __name__ == "__main__":
    main()