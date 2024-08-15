<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") 
    {
        $vorname = htmlspecialchars($_POST['vorname']);
        $nachname = htmlspecialchars($_POST['nachname']);
        $email = htmlspecialchars($_POST['email']);
        $empfohlen = htmlspecialchars($_POST['empfohlen']);

        $recommendedName = explode(' ', $empfohlen);
        //überpfrüfen ob empfohlen bereits in der db vorhandne ist wenn nicht anlegen
        //daten in db schreiben
        //empfohlen in db schreiben mit der kennung aus der db
        $dsn = 'mysql:host=localhost;dbname=userData';
        $username = 'root';
        $password = 'root';

        try 
        {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            // Abfrage, ob der Benutzer mit dieser E-Mail-Adresse existiert
            $sql = 'SELECT COUNT(*) FROM recommended WHERE LastName = :lastname AND FirstName = :firstname';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':lastname', $recommendedName[1]);
            $stmt->bindParam(':firstname', $recommendedName[0]);
            $stmt->execute();
        
            // Anzahl der gefundenen Einträge prüfen
            $count = $stmt->fetchColumn();
        
            if ($count == 0) 
            {
                // E-Mail existiert nicht, also neuen Benutzer hinzufügen
                $sql = 'INSERT INTO recommended (LastName, FirstName) VALUES (:lastname, :firstname)';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':lastname', $recommendedName[1]);
                $stmt->bindParam(':firstname', $recommendedName[0]);
        
                if ($stmt->execute()) 
                {
                    writeToDB($pdo, $vorname, $nachname, $email, $recommendedName);
                    echo 'Neuer Benutzer wurde hinzugefügt.';
                } 
                else 
                {
                    echo 'Fehler beim Hinzufügen des Benutzers.';
                }
            } 
            else 
            { 
                writeToDB($pdo, $vorname, $nachname, $email, $recommendedName);
                echo 'Benutzer existiert bereits.';
            }
        } 
        catch (PDOException $e) 
        {
            echo 'Verbindung fehlgeschlagen: ' . $e->getMessage();
            die();
        }
        
        
        // Verbindung schließen (optional)
        $pdo = null;
    }

    function writeToDB($pdo, $vorname, $nachname, $email, $recommendedName)
    {
        try 
        {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $getID = 'SELECT ID FROM recommended WHERE LastName = :lastname AND FirstName = :firstname';
            $stmt = $pdo->prepare($getID);
            $stmt->bindParam(':lastname', $recommendedName[1]);
            $stmt->bindParam(':firstname', $recommendedName[0]);
            $stmt->execute();
        
            // Id auslesen
            $id = $stmt->fetchColumn();

            // SQL-Abfrage vorbereiten
            $sql = 'INSERT INTO data (LastName, FirstName, Email, recommended) VALUES (:vorname, :nachname, :email, :id)';
            $stmt = $pdo->prepare($sql);

            // Werte binden und Abfrage ausführen
            $stmt->bindParam(':vorname', $vorname);
            $stmt->bindParam(':nachname', $nachname);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) 
            {
                echo 'Daten erfolgreich eingefügt';
            } 
            else 
            {
                echo 'Fehler beim Einfügen der Daten';
            }
        } 
        catch (PDOException $e) 
        {
            echo 'Verbindung fehlgeschlagen: ' . $e->getMessage();
            die();
        }
    }
?>