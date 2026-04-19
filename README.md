# Jersey E-Mart
Jersey E-Mart is the online Jersey shopping site which focuses on Nepal Jersies.
# Checkout Flow:

## Cash on delivery (COD):
1. User adds items to cart
2. Click "Checkout"
3. Fill delivery details
4. Select "Cash on Delivery"
5. Confirm order
6. Order created immediately
7. Redirect to Thank You page

## eSewa Payment:
1. User adds items to cart
2. Click "Checkout"
3. Fill delivery details
4. Select "eSewa"
5. Redirected to eSewa payment gateway
6. User completes payment
7. eSewa redirects to success.php
8. Order created after payment verification
9. Redirect to Thank You page

## 🔒 Security Features
- Password hashing
- SQL prepared statements
- eSewa signature verification
- Session management
- Input validation


Algorithm used in Jersey E-Mart: 
1. Content Based Score Algorithm: This algorithm calculates the relevance of a product 
based on the user’s search keyword. Different product attributes are checked and 
weighted scores are assigned. A higher score indicated higher relevance. 
Algorithm: 
Step 1: Start 
Step 2: For each product in the product list 
Step 3: Initialize score = 0 
Step 4: If keyword matches product name 
score = score + 10 
Step 5: If keyword matches category 
score = score + 5 
Step 6: If keyword matches country 
score = score + 5 
Step 7: If keyword matches quality 
score = score + 3 
Step 8: Assign score to relevance_score of product 
Step 9: Repeat steps 2–8 for all products 
Step 10: Stop 
Use in Project: Used to rank products based on how well they match the search 
keyword. 

2. Collaborative Filtering 
This algorithm recommends products based on overall user behavior. Products with higher 
order counts are considered more popular and are ranked higher. 
Algorithm: 
Step 1: Start 
Step 2: Initialize maxOrders = 0 
Step 3: For each product 
If total_orders > maxOrders 
maxOrders = total_orders 
Step 4: For each product 
Step 5: Set popularity_score = total_orders 
Step 6: If total_orders ≥ 60% of maxOrders 
Mark product as Bestseller 
Else mark as not Bestseller 
Step 7: If total_orders ≥ 5 
Mark product as Trending 
Else mark as not Trending 
Step 8: Calculate final_price: 
final_price = price - (price × discount / 100) 
Step 9: Calculate rank_score: 
rank_score = (relevance_score × 2) + (popularity_score × 0.5) 
Step 10: Convert date into numeric value for comparison 
Step 11: Repeat steps for all products 
Step 12: Stop 
Use in Project: 
Identifies best selling and trending products based on customer purchase behavior. 

3. Bubble Sort: 
Here bubble sort is used to arrange products based on selected criteria such as price, 
discount, popularity or relevance. 
Algorithm: 
Step 1: Start 
Step 2: Let n = number of products 
Step 3: For i = 0 to n-1 
Step 4: For j = 0 to n-i-1 
Step 5: If sorting in ascending order 
If arr[j] > arr[j+1] 
Swap arr[j] and arr[j+1] 
Step 6: If sorting in descending order 
If arr[j] < arr[j+1] 
Swap arr[j] and arr[j+1] 
Step 7: Repeat until all elements are sorted 
Step 8: Stop 
Use in Project: 
Used to sort product listings dynamically based on user preference. 


