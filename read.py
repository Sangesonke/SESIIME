import xlrd
import matplotlib.pyplot as plt

wb = xlrd.open_workbook("Results.xlsx")
ws = wb.sheet_by_index(0)

row_index = 11  # row 12
col_index = 12  # column 13

Sio2 = ws.cell_value(row_index, col_index)
print("SIO2 value:", Sio2)

j = 3  # the column at which the elements start

for i in range(12):
    y = ws.cell_value(row_index, j)
    element = ws.cell_value(row_index - 2, j)
    
    # Check if the column index is 10 or 12, then skip plotting
    if j in [12, 14]:
        j += 1
        continue
    
    plt.figure()
    plt.scatter(Sio2, y, color='blue', marker='o')
    plt.title('Scatter Plot for Sio2 vs ' + str(element)) 
    plt.xlabel('SIO2 (%)') 
    plt.ylabel(str(element) + '(%)') 
    plt.grid(False) 
    plt.show()
    
    j += 1