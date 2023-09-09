<?php
require_once("./db.inc.php");

const UPLOAD_PATH = __DIR__."/uploads/";

// See PHP's upload_max_filesize setting for maximum file size for uploads.
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MiB
const MIME_TYPES = ["image/", "application/pdf"];
const DATABASE = __DIR__."/../db.sql";

if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH);
}

$database = new Database(DATABASE);

$successFullyUploaded = null;
$errors = [];
$formData = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $successFullyUploaded = false;

    // Merge files and comments together in one arrayr
    if (isset($_FILES['files'])) {
        foreach ($_FILES['files'] as $fileKey => $fileValues) {
            foreach ($fileValues as $fileIndex => $fileValue) {
                if (!isset($formData[$fileIndex])) {
                    $formData[$fileIndex] = ['file' => [], 'comment' => null];
                }
                $formData[$fileIndex]['file'][$fileKey] = $fileValue;

                if (isset($_POST['comments'][$fileIndex])) {
                    $formData[$fileIndex]['comment'] = $_POST['comments'][$fileIndex];
                }
            }
        }
    }

    // Cleanup empty uploads
    foreach ($formData as $fileIndex => $fileData) {
        if (!$fileData['file']['name'] && !$fileData['file']['size']) {
            unset($formData[$fileIndex]);
        }
    }

    // Check uploads for allowed size and mime types
    foreach ($formData as $fileIndex => $fileData) {
        if ($fileData['file']['error']) {
            $errors[$fileIndex] = [
                'file' => $fileData['file']['name'],
                'messages' => ['Es ist ein Serverfehler beim Upload aufgetreten. Evtl. ist '],
            ];

            continue;
        }
        if ($fileData['file']['size'] > MAX_FILE_SIZE) {
            if (!isset($errors[$fileIndex])) {
                $errors[$fileIndex] = ['file' => $fileData['file']['name'], 'messages' => []];
            }
            $errors[$fileIndex]['messages'][] = sprintf(
                'Datei darf maximal %.2f MiB groß sein',
                MAX_FILE_SIZE / 1024 / 1024
            );
        }
        $mimeTypeAllowed = false;
        foreach (MIME_TYPES as $mimeType) {
            if (str_starts_with($fileData['file']['type'], $mimeType)) {
                $mimeTypeAllowed = true;
                break;
            }
        }
        if (!$mimeTypeAllowed) {
            if (!isset($errors[$fileIndex])) {
                $errors[$fileIndex] = ['file' => $fileData['file']['name'], 'messages' => []];
            }
            $errors[$fileIndex]['messages'][] = sprintf('Dateiformat %s nicht erlaubt', $fileData['file']['type']);
        }
    }

    // If no error move files to its target location. Upload path is based on current timestamp and random id,
    // filenames are based on the order for the submitted files.
    if (count($errors) === 0) {
        $filesUploaded = 0;
        $uploadDirectory = sprintf('%s/%s-%s', UPLOAD_PATH, time(), uniqid());
        mkdir($uploadDirectory, 0766);
        foreach ($formData as $fileIndex => $fileData) {
            $uploadFile = sprintf('%s/%d', $uploadDirectory, $fileIndex);
            if (move_uploaded_file($fileData['file']['tmp_name'], $uploadFile)) {
                $formData[$fileIndex]['upload'] = $uploadFile;
                $filesUploaded++;
            } else {
                $errors[$fileIndex] = [
                    'file' => $fileData['file']['name'],
                    'messages' => ['Datei konnte nicht gespeichert werden.'],
                ];
            }
        }

        $successFullyUploaded = $filesUploaded === count($formData);
    }

    if ($successFullyUploaded) {
        foreach ($formData as $fileIndex => $fileData) {
            $database->addEntry(
                $fileData['upload'],
                $fileData['file']['name'],
                $fileData['file']['type'],
                $fileData['file']['size'],
                $fileData['comment']
            );
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Datei-Upload</title>
    <style>
      :root {
        font-family: Arial, Helvetica, sans-serif;
      }

      table {
        border: solid;
        border-collapse: collapse;
      }

      th, td {
        border: 1px solid #ccc;
        padding: 4px;
      }

      .success {
        border: 2px solid green;
      }

      .error {
        border: 2px solid red;
      }

      .fieldGroup {
        border: 1px solid grey;
        padding: 10px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    </style>
</head>
<body>
<?php if ($successFullyUploaded === true): ?>
    <div class="success">
        <h2>Upload erfolgreich:</h2>
        <ul>
            <?php foreach ($formData as $fileData): ?>
                <li>
                    <strong><?= htmlentities($fileData['file']['name']); ?></strong>
                    <?php if ($fileData['comment']): ?>
                        <em>(<?= htmlentities($fileData['comment']); ?>)</em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($successFullyUploaded === false): ?>
    <div class="error">
        <h2>Es sind Fehler aufgetreten:</h2>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li>
                    <strong><?= htmlentities($error['file']); ?></strong>
                    <ul>
                        <?php foreach ($error['messages'] as $errorMessage): ?>
                            <li>
                                <?= htmlentities($errorMessage); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<h1>Datei-Upload</h1>
<form id="form" action="index.php" method="post" enctype="multipart/form-data">
    <div id="fields">
        <div class="fieldGroup">
            <input type="file" name="files[0]" accept="image/*, application/pdf"/>
            <input type="text" name="comments[0]" placeholder="Bemerkung"/>
        </div>
    </div>
    <br>
    <input type="button" value="Weitere Datei hinzufügen" onClick="addField()"/>
    <br>
    <input type="submit" value="Dateien hochladen"/>
</form>

<?php $uploadedFiles = $database->getAllEntries(); ?>
<?php if ($uploadedFiles !== false && count($uploadedFiles) > 0): ?>
    <h2>Upload-Verlauf:</h2>
    <table>
        <thead>
        <tr>
            <th>Dateiname</th>
            <th>Typ</th>
            <th>Größe</th>
            <th>Kommentar</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($uploadedFiles as $uploadedFile): ?>
            <tr>
                <td>
                    <a href="download.php?id=<?= htmlentities($uploadedFile['id']); ?>"><?= htmlentities(
                            $uploadedFile['filename']
                        ); ?></a>
                </td>
                <td>
                    <?= htmlentities($uploadedFile['mime_type']); ?>
                </td>
                <td>
                    <?= htmlentities(sprintf('%.2f MiB', $uploadedFile['size_bytes'] / 1024 / 1024)); ?>
                </td>
                <td>
                    <?= $uploadedFile['comment'] ? htmlentities($uploadedFile['comment']) : ''; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
  function addField () {
    const existingFilesCount = document.getElementsByClassName('fieldGroup').length
    const newFieldGroup = document.createElement('div')
    newFieldGroup.classList.add('fieldGroup')

    const newFileInput = document.createElement('input')
    newFileInput.type = 'file'
    newFileInput.accept = 'image/*, application/pdf'
    newFileInput.name = 'files[' + existingFilesCount + ']'

    const newCommentInput = document.createElement('input')
    newCommentInput.type = 'text'
    newCommentInput.name = 'comments[' + existingFilesCount + ']'
    newCommentInput.placeholder = 'Bemerkung'

    newFieldGroup.appendChild(newFileInput)
    newFieldGroup.appendChild(newCommentInput)

    const fieldContainer = document.getElementById('fields')
    fieldContainer.appendChild(newFieldGroup)
  }
</script>
</body>
</html>
