<?php

class Booking
{
    private $dbh;
    private $bookingsTableName = 'bookings';

    public function __construct($database, $host, $databaseUsername, $databaseUserPassword)
    {
        try {
            $this->dbh = new PDO(sprintf('mysql:host=%s;dbname=%s', $host, $database), $databaseUsername, $databaseUserPassword);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function index()
    {
        $statement = $this->dbh->query('SELECT * FROM ' . $this->bookingsTableName);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add(DateTimeImmutable $bookingDate, $timeSlot, $included, $firstName, $lastName, $contactNo, $email, $address, $amount)
    {
        $statement = $this->dbh->prepare(
            'INSERT INTO ' . $this->bookingsTableName . ' (booking_date, time_slot, included, firstName, lastName, contactNo, email, address, amount) VALUES (:bookingDate, :timeSlot, :included, :firstName, :lastName, :contactNo, :email, :address, :amount)'
        );

        if (false === $statement) {
            throw new Exception('Invalid prepare statement');
        }

        if (false === $statement->execute([
                ':bookingDate' => $bookingDate->format('Y-m-d H:i:s'),
                ':timeSlot' => $timeSlot,
                ':included' => $included,
                ':firstName' => $firstName,
                ':lastName' => $lastName,
                ':contactNo' => $contactNo,
                ':email' => $email,
                ':address' => $address,
                ':amount' => $amount

            ])) {
            throw new Exception(implode(' ', $statement->errorInfo()));
        }
    }

    public function delete($id)
    {
        $statement = $this->dbh->prepare(
            'DELETE from ' . $this->bookingsTableName . ' WHERE id = :id'
        );
        if (false === $statement) {
            throw new Exception('Invalid prepare statement');
        }
        if (false === $statement->execute([':id' => $id])) {
            throw new Exception(implode(' ', $statement->errorInfo()));
        }
    }
}

?>

<?php
