<?php

namespace App\Http\Controllers;

use App\Mail\Kontakt;
use App\Mail\Spende;
use App\Mail\Sprachdatei;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelLocalization;
use Mail;
use ZipArchive;

class MailController extends Controller
{
    /**
     * Load Startpage accordingly to the given URL-Parameter and Mobile
     *
     * @param  int  $id
     * @return Response
     */
    public function contactMail(Request $request)
    {

        # Nachricht, die wir an den Nutzer weiterleiten:
        $messageType   = ""; # [success|error]
        $returnMessage = '';
        $replyTo       = $request->input('email', 'noreply@metager.de');
        if ($replyTo === "") {
            $replyTo = "noreply@metager.de";
        } else {
            $replyTo = $request->input('email');
        }

        if (!$request->has('message')) {
            $messageType   = "error";
            $returnMessage = "Tut uns leid, aber leider haben wir mit Ihrer Kontaktanfrage keine Daten erhalten. Die Email wurde nicht versand";
        } else {
            # Wir versenden die Mail des Benutzers an uns:
            $message = $request->input('message');

            Mail::to("office@suma-ev.de")
                ->send(new Kontakt($replyTo, $message));

            $returnMessage = 'Ihre Email wurde uns erfolgreich zugestellt. Vielen Dank dafür! Wir werden diese schnellstmöglich bearbeiten und uns dann ggf. wieder bei Ihnen melden.';
            $messageType   = "success";
        }

        return view('kontakt.kontakt')
            ->with('title', 'Kontakt')
            ->with('js', ['openpgp.min.js', 'kontakt.js'])
            ->with($messageType, $returnMessage);
    }

    public function donation(Request $request)
    {
        # Der enthaltene String wird dem Benutzer nach der Spende ausgegeben
        $messageToUser = "";
        $messageType   = ""; # [success|error]

        #Sicherheitsüberprüfung (Wir wurden in letzter Zeit ziemlich mit Mails zugespammt
        # Wir überprüfen also, ob das Feld für die Kontonummer tatsächlich eine Kontonummer, oder eine IBAN enthält:
        $iban = $request->input('Kontonummer', '');
        $iban = preg_replace("/\s/s", "", $iban);
        # Eine Kontonummer besteht nur aus Zahlen
        # Eine IBAN besteht aus einem Ländercode (2 Buchstaben), einer 2 stelligen Prüfsumme (2 Ziffern) gefolgt von
        # einer Kombination aus Buchstaben und Zahlen
        if (!preg_match("/^\d+$/s", $iban) && !preg_match("/^[a-zA-Z]{2}\d{2}[a-zA-Z0-9]+$/s", $iban)) {
            $messageToUser = "Die eingegebene IBAN/Kontonummer scheint nicht Korrekt zu sein. Nachricht wurde nicht gesendet";
            $messageType   = "error";
        } else {

            # Folgende Felder werden vom Spendenformular als Input übergeben:
            # Name
            # Telefon
            # email
            # Kontonummer ( IBAN )
            # Bankleitzahl ( BIC )
            # Nachricht
            if (!$request->has('Kontonummer') || !$request->has('Bankleitzahl') || !$request->has('Nachricht')) {
                $messageToUser = "Sie haben eins der folgenden Felder nicht ausgefüllt: IBAN, BIC, Nachricht. Bitte korrigieren Sie Ihre Eingabe und versuchen es erneut.\n";
                $messageType   = "error";
            } else {
                $message = "\r\nName: " . $request->input('Name', 'Keine Angabe');
                $message .= "\r\nTelefon: " . $request->input('Telefon', 'Keine Angabe');
                $message .= "\r\nKontonummer: " . $request->input('Kontonummer');
                $message .= "\r\nBankleitzahl: " . $request->input('Bankleitzahl');
                $message .= "\r\nNachricht: " . $request->input('Nachricht');

                $replyTo = $request->input('email', 'anonymous-user@metager.de');
                if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                    $messageToUser .= "Die eingegebene Email-Addresse ($replyTo) scheint nicht korrekt zu sein.";
                }

                try {
                    Mail::to("office@suma-ev.de")
                        ->send(new Spende($replyTo, $message));

                    $messageType   = "success";
                    $messageToUser = "Herzlichen Dank!! Wir haben Ihre Spendenbenachrichtigung erhalten.";
                } catch (\Swift_TransportException $e) {
                    $messageType   = "error";
                    $messageToUser = 'Beim Senden Ihrer Spendenbenachrichtigung ist ein Fehler auf unserer Seite aufgetreten. Bitte schicken Sie eine Email an: office@suma-ev.de, damit wir uns darum kümmern können.';
                }
            }
        }

        if ($messageType === "error") {
            $request->flash();
            return view('spende.spende')
                ->with('title', 'Kontakt')
                ->with($messageType, $messageToUser);
        } else {
            $data = ['name' => $request->input('Name', 'Keine Angabe'), 'telefon' => $request->input('Telefon', 'Keine Angabe'), 'kontonummer' => $request->input('Kontonummer'), 'bankleitzahl' => $request->input('Bankleitzahl'), 'email' => $request->input('email', 'anonymous-user@metager.de'), 'nachricht' => $request->input('Nachricht')];
            $data = base64_encode(serialize($data));
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route("danke", ['data' => $data])));
        }

    }

    #Ueberprueft ob ein bereits vorhandener Eintrag bearbeitet worden ist
    private function isEdited($k, $v, $filename)
    {
        try {
            $temp = include resource_path()."/".$filename;
            foreach ($temp as $key => $value) {
                if($k === $key && $v !== $value) {
                    return true;
                }
            }  
        } catch (\ErrorException $e) {
            #Datei existiert noch nicht
            return true;
        }
        return false;
    }

    private function extractLanguage($key) 
    {   
        #Kürzt bspw. "_new_de_redirect bzw. "de_redirect" zu "de"
        preg_match("/^(?:_new_)?([^_]*)/", $key, $matches);
        foreach($matches as $dir) {
            if(strlen($dir) == 2)
                return $dir;
            }
    }

    private function processKey($key) 
    {   
        $key = trim($key);
        #Kürzt bspw. "_new_de_redirect bzw. "de_redirect" zu "redirect"
        preg_match("/^(?:_new_)?(?:[^_]*)_(\w*.?\w*#?.?\w*)/", $key, $matches);
        foreach($matches as $processedKey) {
            if(strpos($processedKey, "_") === FALSE) {
                return $processedKey;
            }
        }
        return $key;
    }

    public function sendLanguageFile(Request $request, $from, $to, $exclude = "", $email ="")
    {
        $filename = $request->input('filename');
        # Wir erstellen nun zunächst den Inhalt der Datei:
        $data = [];
        $new  = 0;
        $emailAddress = "";
        $editedKeys = "";
        foreach ($request->all() as $key => $value) {

            if ($key === "filename" || $value === "") {
                continue;
            }
            if($key === "email") {
                $emailAddress = $value;
                continue;
            }
            $key = base64_decode($key);
            if (strpos($key, "_new_") === 0 && $value !== "") {
                $new++;
                $key = substr($key, strpos($key, "_new_") + 5);
                $editedKeys = $editedKeys."\n".$key;

            } else if ($this->isEdited($key, $value, $filename)) {
                $new++;
                $editedKeys = $editedKeys."\n".$key;
            }

            $key = trim($key);
            if (!strpos($key, "#")) {
                $data[$key] = $value;
            } else {
                $ref = &$data;
                do {
                    $ref = &$ref[substr($key, 0, strpos($key, "#"))];
                    $key = substr($key, strpos($key, "#") + 1);
                } while (strpos($key, "#"));
                $ref = &$ref[$key];
                $ref = $value;
            }
        }

        $output = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $output = preg_replace("/\{/si", "[", $output);
        $output = preg_replace("/\}/si", "]", $output);
        $output = preg_replace("/\": ([\"\[])/si", "\"\t=>\t$1", $output);
        
        $output = "<?php\n\nreturn $output;\n";

        $message = "Moin moin,\n\nein Benutzer hat eine Sprachdatei aktualisiert.\nBearbeitet wurden die Einträge: $editedKeys\n\nSollten die Texte so in Ordnung sein, ersetzt, oder erstellt die Datei aus dem Anhang in folgendem Pfad:\n$filename\n\nFolgend zusätzlich der Inhalt der Datei:\n\n$output";

        # Wir haben nun eine Mail an uns geschickt, welche die entsprechende Datei beinhaltet.
        # Nun müssen wir den Nutzer eigentlich nur noch zurück leiten und die Letzte bearbeitete Datei ausschließen:
        $ex = [];
        if ($exclude !== "") {
            try {
                $ex = unserialize(base64_decode($exclude));
            } catch (\ErrorException $e) {
                $ex = [];
            }

            if (!isset($ex["files"])) {
                $ex["files"] = [];
            }
        }
        if (!isset($ex["new"])) {
            $ex["new"] = 0;
        }
        $ex['files'][] = basename($filename);
        $ex["new"] += $new;

        if ($new > 0) {
            if($emailAddress !== "") { 
                Mail::to("dev@suma-ev.de")
                ->send(new Sprachdatei($message, $output, basename($filename), $emailAddress));
            } else {
                Mail::to("dev@suma-ev.de")
                ->send(new Sprachdatei($message, $output, basename($filename)));
            }
        }
        $ex = base64_encode(serialize($ex));

        return redirect(url('languages/edit', ['from' => $from, 'to' => $to, 'exclude' => $ex, 'email' => $emailAddress]));
    }



    public function processSynopticPageInput(Request $request, $exclude = "") {

        $filename = $request->input('filename');

        #Identifizieren des gedrückten Buttons
        if(isset($request['nextpage'])) {
            #Leite weiter zur nächsten Seite
            $ex = [];

            if ($exclude !== "") {
                try {
                    $ex = unserialize(base64_decode($exclude));
                } catch (\ErrorException $e) {
                    $ex = [];
                }

                if (!isset($ex["files"])) {
                    $ex["files"] = [];
                }
            }
            if (!isset($ex["new"])) {
                $ex["new"] = 0;
            }
            $ex['files'][] = basename($filename);
            $ex = base64_encode(serialize($ex));

            return redirect(url('synoptic', ['exclude' => $ex]));
        }

        #Andernfalls auslesen, zippen und herunterladen der veränderten Dateien 
        $data = [];
        $new  = 0;
        $editedFiles = [];

        foreach ($request->all() as $key => $value) {

            if ($key === "filename" || $value === "") {
                continue;
            }

            $key = base64_decode($key);

            #Pfad zur Datei anhand des Schlüsselnamens rekonstruieren (Schlüssel enthält Sprachkürzel)
            $langdir = $this->extractLanguage($key);
            $filepath = "lang/".$langdir."/".$filename;
 
            if (strpos($key, "_new_") === 0 && $value !== "" || $this->isEdited($this->processKey($key), $value, $filepath)) {
                $new++;
                $editedFiles[$langdir] = $filepath;
            } 
        }     

        #Erneute Iteration über Request, damit Dateien mitsamt vorherigen Einträgen abgespeichert werden 
        foreach($request->all() as $key => $value) {

            if ($key === "filename" || $value === "") {
                continue;
            }

            $key = base64_decode($key);

            #Pfad zur Datei anhand des Schlüsselnamens rekonstruieren (Schlüssel enthält Sprachkürzel)
            $langdir = $this->extractLanguage($key);

            #Überspringe Datei, falls diese nicht bearbeitet worden ist
            if(!isset($editedFiles[$langdir])) {
                continue;
            }

            #Key kuerzen, sodass er nur den eigentlichen Keynamen enthält
            $key = $this->processKey($key);
            
            if (!strpos($key, "#")) {
                $data[$langdir][$key] = $value;
            #Aufdröseln von 2D-Arrays
            } else {
                $ref = &$data;
                do {
                    $ref = &$ref[$langdir][substr($key, 0, strpos($key, "#"))];
                    $key = substr($key, strpos($key, "#") + 1);
                } while (strpos($key, "#"));
                $ref = &$ref[$key];
                $ref = $value;
            }
        }

        if(empty($data)) {
            return redirect(url('synoptic', ['exclude' => $exclude]));
        }

        $zip = new ZipArchive();

        if ($zip->open("langfiles.zip", ZipArchive::OVERWRITE)!==TRUE) {  
        } else if ($zip->open("langfiles.zip", ZipArchive::CREATE)!==TRUE) {
            exit("Cannot open".$filename);
        }

        #Erstelle Ausgabedateien
        foreach($data as $lang => $entries) {
            $output = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $output = preg_replace("/\{/si", "[", $output);
            $output = preg_replace("/\}/si", "]", $output);
            $output = preg_replace("/\": ([\"\[])/si", "\"\t=>\t$1", $output);
            $output = "<?php\n\nreturn $output;\n";
            $zip->addEmptyDir($lang);
            $zip->addFromString($lang."/".$filename, $output);
        }

        $zip->close();

        return response()->download("langfiles.zip", $filename.".zip");


    }



}
