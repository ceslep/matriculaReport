import os
from dotenv import load_dotenv
import mysql.connector

# Cargar variables desde .env
load_dotenv()

MYSQL_HOST = os.getenv('MYSQL_HOST')
MYSQL_PORT = os.getenv('MYSQL_PORT')
MYSQL_USER = os.getenv('MYSQL_USER')
MYSQL_PASSWORD = os.getenv('MYSQL_PASSWORD')
MYSQL_DB = os.getenv('MYSQL_DB')

try:
    conn = mysql.connector.connect(
        host=MYSQL_HOST,
        port=MYSQL_PORT,
        user=MYSQL_USER,
        password=MYSQL_PASSWORD,
        database=MYSQL_DB
    )
    cursor = conn.cursor()

    # Obtener lista de tablas
    cursor.execute("SHOW TABLES;")
    ts=cursor.fetchall()
    print(ts)
    tables = [table[0] for table in cursor.fetchall()]

    tablas_con_year = []

    for table in tables:
        # Consultar las columnas de la tabla
        cursor.execute(f"SHOW COLUMNS FROM `{table}`;")
        columns = [col[0].lower() for col in cursor.fetchall()]
        if "year" in columns:
            # Contar registros solo si tiene el campo year
            cursor.execute(f"SELECT COUNT(*) FROM `{table}`;")
            count = cursor.fetchone()[0]
            tablas_con_year.append((table, count))

    # Mostrar el resultado
    if tablas_con_year:
        print("Tablas que poseen el campo 'year':")
        for nombre, cantidad in tablas_con_year:
            print(f"{nombre}: {cantidad} registros")
    else:
        print("No hay tablas con el campo 'year'.")

    cursor.close()
    conn.close()

except mysql.connector.Error as err:
    print(f"Error: {err}")
