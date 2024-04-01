import xlrd
import matplotlib.pyplot as plt


# Open the Excel file
wb = xlrd.open_workbook("Results.xlsx")

# Specify the sheet you want to work with (here, we choose the first sheet)
ws = wb.sheet_by_index(0)

# Specify the row index (0-based) and column index (0-based) of the cell you want to extract
row_index = 11  # Example: row 12
col_index = 12  # Example: column 13

# Get the value of the cell at the specified row and column indices
Sio2 = ws.cell_value(row_index, col_index)
# Print the value of the cell
print("SIO2 value:", Sio2)



# Create scatter plot
j = 3 #the column at which the elements start
for i in range(12):
    y = ws.cell_value(row_index,j)
    plt.scatter(Sio2, y, color='blue', marker='o')  # 'o' for circle markers
    j = j+1

# Add title and labels
plt.title('Scatter Plot Example')
plt.xlabel('X-axis')
plt.ylabel('Y-axis')

# Show plot
plt.grid(False)  # Add grid for better visualization
plt.show()