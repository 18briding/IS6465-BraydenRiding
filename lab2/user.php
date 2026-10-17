<?php

// Lab 2C: Constructors, Constants, and Inheritance

echo '<h1>Lab 2C: User and Tutor Classes</h1>';


// TODO: Return the value with extra spaces removed.

function cleanText($value)
{
    return trim($value);
}

class User
{
    public const STATUS_ACTIVE = 'active';

    public $name;
    public $email;
    public $status;

    public function __construct($name, $email)
    {
        $this->name = cleanText($name);    // TODO: Clean and store the name.
        $this->email = strtolower(cleanText($email));  // TODO: Clean, lowercase, and store the email.
        $this->status = self::STATUS_ACTIVE;    // TODO: Assign STATUS_ACTIVE to status.
    }
}

class Tutor extends User
{
    public $subject;

    public function __construct($name, $email, $subject)
    {
        parent::__construct($name, $email);    // TODO: Call the parent constructor.
        $this->subject = cleanText($subject);    // TODO: Clean and store the subject.
    }

    public function profileSummary()
    {
        return $this->name . ' (' . $this->email . ')' .
                ' - Tutor for ' . $this->subject;

        // TODO: Return the tutor's name, email, and subject.

    }
}

$tutor = new Tutor(' Jodan Lee ', 'JLEE@EXAMPLE.COM', 'PHP');
echo $tutor->profileSummary();


// TODO: Create a Tutor object using sample data.
// TODO: Display profileSummary().