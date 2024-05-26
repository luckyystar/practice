<?php

class BookableCell
{
    /**
     * @var Booking
     */
    private $booking;
 
    private $currentURL;
 
    /**
     * BookableCell constructor.
     * @param Booking $booking
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->currentURL = htmlentities($_SERVER['REQUEST_URI']);
    }
 
    public function update(Calendar $cal)
    {
        if ($this->isDateBooked($cal->getCurrentDate())) {
            $cal->cellContent = $this->bookedCell($cal->getCurrentDate());
        } else {
            $cal->cellContent = $this->openCell($cal->getCurrentDate());
        }
    }
 
    public function routeActions()
    {
        if (isset($_POST['delete'])) {
            $this->deleteBooking($_POST['id']);
        }
 
        if (isset($_POST['add'])) {
            if (
                isset($_POST['date'], $_POST['time_slot'], $_POST['included'],
                      $_POST['firstName'], $_POST['lastName'], $_POST['contactNo'], $_POST['email'], $_POST['address'], $_POST['deposit_amount'], $_POST['balance_amount'], $_POST['amount'])
            ) {
                $this->addBooking(
                    $_POST['date'], $_POST['time_slot'], $_POST['included'],
                    $_POST['firstName'], $_POST['lastName'], $_POST['contactNo'],
                    $_POST['email'], $_POST['address'], $_POST['deposit_amount'], $_POST['balance_amount'], $_POST['amount']
                );
            }
        }
    }
 
    private function openCell($date)
    {
        return '<div class="open">' . $this->bookingForm($date) . '</div>';
    }
 
    private function bookedCell($date)
    {
        return '<div class="booked">' . $this->deleteForm($this->bookingId($date)) . '</div>';
    }
 
    private function isDateBooked($date)
    {
        return in_array($date, $this->bookedDates());
    }
 
    private function bookedDates()
    {
        return array_map(function ($record) {
            return $record['booking_date'];
        }, $this->booking->index());
    }
 
    private function bookingId($date)
    {
        $booking = array_filter($this->booking->index(), function ($record) use ($date) {
            return $record['booking_date'] === $date;
        });
 
        $result = array_shift($booking);
 
        return $result['id'];
    }
 
    private function deleteBooking($id)
    {
        $this->booking->delete($id);
    }
 
    private function addBooking($date, $timeSlot, $included, $firstName, $lastName, $contactNo, $email, $address, $deposit_amount, $balance_amount, $amount)
{
    // Define time slots and their corresponding start times
    $timeSlotStartTimes = [
        '8am - 5pm' => '08:00:00',
        '12nn - 8pm' => '12:00:00',
        '2pm - 10pm' => '14:00:00',
    ];

    // Get the start time for the selected time slot
    $startTime = $timeSlotStartTimes[$timeSlot] ?? null;

    if ($startTime) {
        // Combine date and start time to create the booking datetime string
        $bookingDateTimeString = $date . ' ' . $startTime;

        try {
            // Create a DateTimeImmutable object from the booking datetime string
            $bookingDateTime = new DateTimeImmutable($bookingDateTimeString);

            // Calculate the amount based on the selected included option
            $amount = 8000;
            if ($included === 'LPG gas and Stove') {
                $amount = $amount + 250;
            }
            $deposit_amount = 1000;
            $balance_amount = $amount - $deposit_amount;

            // Add the booking with the constructed DateTimeImmutable object
            $this->booking->add($bookingDateTime, $timeSlot, $included, $firstName, $lastName, $contactNo, $email, $address, $deposit_amount, $balance_amount, $amount);

            // Return success message with booking details
            return "Booking successful! Details: Date: $date, Time Slot: $timeSlot, Included: $included, First Name: $firstName, Last Name: $lastName, Contact No.: $contactNo, Email: $email, Address: $address, Deposit Amount: . number_format($deposit_amount), Balance Amount: . number_format($balance_amount), Amount: " . number_format($amount);
        } catch (Exception $e) {
            // Handle any exceptions, such as invalid date or time format
            return 'Error: ' . $e->getMessage();
        }
    } else {
        return 'Invalid time slot selected.';
    }
}

    
private function bookingForm($date)
{
    $amount = 8000; // Initialize amount
    $deposit_amount = 0.00;
    $balance_amount = 0.00;
    // Check if date matches POST data and calculate amount
    if (isset($_POST['date']) && $_POST['date'] === $date && isset($_POST['included'])) {
        if ($_POST['included'] === 'LPG gas and Stove') {
            $amount = $amount + 250;
        }
        $deposit_amount = 1000;
        $balance_amount = $amount - $deposit_amount;
    } 
    // Form HTML
    if (isset($_POST['add']) && $_POST['date'] === $date) {
        return '
        <div class="reservation-box">
            <form class="reservation_form" method="post" action="' . $this->currentURL . '">
                <input type="hidden" name="add" value="1" />
                <input type="hidden" name="date" value="' . $date . '" />      
                <div class="form-group">
                <label for="included">Include Others:</label>
                <select name="included" id="included" required onchange="this.form.submit()">
                    <option value="" disabled' . (empty($_POST['included']) ? ' selected' : '') . '>Select an option</option>
                    <option value="LPG gas and Stove"' . (isset($_POST['included']) && $_POST['included'] === 'LPG gas and Stove' ? ' selected' : '') . '>LPG gas and Stove (+250)</option>
                    <option value="N/A"' . (isset($_POST['included']) && $_POST['included'] === 'N/A' ? ' selected' : '') . '>N/A</option>
                </select>
            </div>          
                <div class="form-group">
                    <label for="time_slot">Time Slot:</label>
                    <select name="time_slot" id="time_slot" required>
                        <option value="" disabled selected>Select a time slot</option>
                        <option value="8am - 5pm"' . (isset($_POST['time_slot']) && $_POST['time_slot'] === '8am - 5pm' ? ' selected' : '') . '>8am - 5pm</option>
                        <option value="12nn - 8pm"' . (isset($_POST['time_slot']) && $_POST['time_slot'] === '12nn - 8pm' ? ' selected' : '') . '>12nn - 8pm</option>
                        <option value="2pm - 10pm"' . (isset($_POST['time_slot']) && $_POST['time_slot'] === '2pm - 10pm' ? ' selected' : '') . '>2pm - 10pm</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <input type="text" name="firstName" id="firstName" value="' . ($_POST['firstName'] ?? '') . '" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name:</label>
                    <input type="text" name="lastName" id="lastName" value="' . ($_POST['lastName'] ?? '') . '" required>
                </div>
                <div class="form-group">
                    <label for="contactNo">Contact No.:</label>
                    <input type="number" name="contactNo" id="contactNo" value="' . ($_POST['contactNo'] ?? '') . '" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" value="' . ($_POST['email'] ?? '') . '" required>
                </div>
                <div class="form-group">
                    <label for="address">Residential Address:</label>
                    <input type="text" name="address" id="address" value="' . ($_POST['address'] ?? '') . '" required>
                </div>
                <div class="form-group">
                <label for="deposit_amount">Deposit Amount: </label>
                <input type="text" name="deposit_amount" id="deposit_amount" value="' . number_format($deposit_amount) . '"required>
            </div>
                <div class="form-group">
                <label for="balance_amount">Balance Amount: </label>
                <input type="text" name="balance_amount" id="balance_amount" value="' . number_format($balance_amount) . '"required>
            </div>
            <!-- Add other form fields here -->
            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="text" name="amount" id="amount" value="' . number_format($amount) . '">
            </div>
                <button class="submitReservation" type="submit">Submit</button>
            </form>
        </div>';
    } else {
        // Display only the "Book" button if it's not clicked
        return '
            <form method="post" action="' . $this->currentURL . '">
                <input type="hidden" name="add" />
                <input type="hidden" name="date" value="' . $date . '" />
                <input class="submit" type="submit" value="Book" />
            </form>';
    }
}
    private function deleteForm($id)
    {
        return '
            <form onsubmit="return confirm(\'Are you sure to cancel?\');" method="post" action="' . $this->currentURL . '">
                <input type="hidden" name="delete" />
                <input type="hidden" name="id" value="' . $id . '" />
                <input class="submit" type="submit" value="Delete" />
            </form>';
    }
}
?>