<?php

declare(strict_types=1);

function get_username(object $pdo, string $username) {
    $query = "SELECT Username FROM Users WHERE Username = :username;";
    $stmt = $pdo-> prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $result = $stmt-> fetch(PDO::FETCH_ASSOC);
    return $result;

}

function get_email(object $pdo, string $email) {
    $query = "SELECT Email FROM Users WHERE Email = :email;";
    $stmt = $pdo-> prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $result = $stmt-> fetch(PDO::FETCH_ASSOC);
    return $result;

}

function set_user(object $pdo, string $username, string $password, string $email, string $address) {
    $query = "INSERT INTO Users (Username, Password, Email, Address) VALUES (:username, :password, :email, :address)";

    $stmt = $pdo->prepare($query);

    $options = ['cost' => 12];
    $hashedPwd = password_hash($password, PASSWORD_BCRYPT, $options);

    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $hashedPwd);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":address", $address);
    $stmt->execute();

}

function set_buyer(object $pdo, string $username) {
    try {
        // First, retrieve the user's ID based on the username
        $query = "SELECT UserID FROM Users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $userid = $result['UserID'];
            $roleid = 2;

            // Next, insert the user's ID and role into the UserRole table
            $query = "INSERT INTO UserRole (UserID, RoleID) VALUES (:userid, :roleid)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':userid', $userid);
            $stmt->bindParam(':roleid', $roleid);

            if ($stmt->execute()) {
                return true; // Success
            } else {
                return false; // Error inserting data
            }
        } else {
            return false; // User not found
        }
    } catch (PDOException $e) {
        return false; // Error handling the query
    }
}
    