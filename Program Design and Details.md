### Files: ###
one html, a php for all algorithm and two txt files  
1. poker.html: buttons, dropdown box, buttton effects, textual content, communication of data with poker.php  
2. poker.php: stores most of the function declarations, read from and write to txt file, compare players' hands, send players' hand and winner info back to poker.html  
3. two txt files: store informaiton of deck of cards (new and remaining deck) in the form of long string of ints   seperated by ",".

### Technical Details: ###
* Ajax
I used ajax to send data encoded by json between poker.php and poker.html
* Card encoding map: club:1-13 diamond:100-1300 heart:10000-130000 spade:1000000-13000000
