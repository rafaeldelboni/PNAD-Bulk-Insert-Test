#!/Python27/python
import traceback, sys
import re, string
import time
import MySQLdb

print "Content-type: text/html"
print
print "<html>"
print "<head>"
print "<title>PNAD</title>"
print "</head>"
print "<body>"

try:
	# Calculate time
	time_start = time.time()

	# Read Column mapping from SAS
	sas_file = "sas/Input PNADC_1Tri_2012 a 3Tri_2015.sas";
	sas_columns = [];

	with open(sas_file) as f:
	    lines = [line.rstrip('\n') for line in f]

	for line in lines:
		# Checks if is an SAS instruction line
		if line[:1] == '@':
	   		line = re.sub(r"\s+"," ",line) 
			tmp_columns =  line.split(" ", 3)
			tmp_columns[0] = int(re.sub("@", "", tmp_columns[0]))-1
	   		tmp_columns[2] = int(string.replace(tmp_columns[2].split(".")[0], '$', ''))
	   		sas_columns.append(tmp_columns)
	
	# Create sql insert header
	table = "pnad_trimensal"
	sql_insert_pnad_header = "INSERT INTO `" + table + "` ("

	for column in sas_columns:
		sql_insert_pnad_header = sql_insert_pnad_header + "`" + column[1]  + "`"
		if column == sas_columns[-1]:
			sql_insert_pnad_header += ") VALUES ("
		else:
			sql_insert_pnad_header += ",";		

	# Create COnnection
	db=MySQLdb.connect(host="localhost",user="root",passwd="",db="test", connect_timeout=2000)
	c=db.cursor()

	# Read data file
	data_file = "data/PNADC_012012.txt"
	data_lines = 0

	with open(data_file) as f:
	    lines = [line.rstrip('\n') for line in f]

	for line in lines:
		sql_insert_pnad_row = ""
		for column in sas_columns:
			sql_insert_pnad_row += "'" + line[column[0]:column[0]+column[2]].strip() + "'";
			if column == sas_columns[-1]:
				sql_insert_pnad_row += ")"
			else:
				sql_insert_pnad_row += ","
		#Insert here!
		c.execute(sql_insert_pnad_header + sql_insert_pnad_row)
		data_lines += 1
	
	# Commit your changes
	db.commit()
	# Close Database
	db.close()

	# Execution time in minutes
	execution_time = time.time() - time_start

	# Execution time of the script
	print '<b>Total Execution Time:</b> ' + str(execution_time) + ' secs <br>'
	print '<b>Processed lines:</b> ' + str(data_lines) + '<br>'
	print '<b>Sample Line:</b> <br>'
	print '<pre>'
	print sql_insert_pnad_header + sql_insert_pnad_row
	print '</pre>'
except:
    print "<pre>{0}</pre>".format(traceback.format_exc())

print "</body>"
print "</html>"