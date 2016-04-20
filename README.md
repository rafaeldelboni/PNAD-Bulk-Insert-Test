# PNAD - Bulk Insert Test (PHP vs Python)
Extract data from a SAS data file, that is pretty much a text with the columns delimited by a fixed width.  
The code reads a .sas file, that contains a map to the columns size and then reads the huge file of 500k rows and 130 columns and insert in a MySQL table.

### Installation
1. Unzip the file in the folder `data`  
    The scripts expects something like this:
    ```
    data\PNADC_012012.txt
    ```

2. In MySQL create the table (pnad_trimensal), script in folder `sql`.

3. Find the MySQL configuration inside the scripts and change as you need.  
    PHP line: 58  
    Python line: 47

### Performance
**PHP 5.6:** Execution Time: 124,76 secs  
**Python 2.7** Execution Time: 165,47 secs  
**Test Processor:** Intel i7-5930k @ 3.5GHz 

### Help
Please if you see some way better to do this, any codecommit or tip is much appreciated.