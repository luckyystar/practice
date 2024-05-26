<html>
<head>
    <link href="calendar.css" type="text/css" rel="stylesheet"/>
</head>
<body>
<?php
include 'Calendar.php';
include 'Booking.php';
include 'BookableCell.php';
 
 
$booking = new Booking(
    'tutorial',
    'localhost',
    'root',
    ''
);
 
$bookableCell = new BookableCell($booking);
 
$calendar = new Calendar();
 
$calendar->attachObserver('showCell', $bookableCell);
 
$bookableCell->routeActions();

echo $calendar->show();
//changesheys
//changesmqkdnmwjdnwjbdfhw
//changes(2)
//changes(3)
//changes (4)
//crcrtrcununhuq
?>
</body>
</html>