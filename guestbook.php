<?php

namespace WeatherMaster;

include_once __DIR__ . "/helpers/pathHelper.php";
include_once __DIR__ . "/base.php";

use WeatherMaster\BasePage;
use DOMDocument; 

class GuestbookPage extends BasePage
{
    private string $xmlFilePath;
    private string $htmlTable = '';
    private string $currentData = '';

    public function __construct()
    {
        parent::__construct("Гостьова книга (XML)");
        $this->xmlFilePath = __DIR__ . "/data/guestbook.xml";
        $this->ensureXmlExists();
    }

    private function ensureXmlExists(): void
    {
        if (!file_exists($this->xmlFilePath)) {
            $defaultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<guestbook>
    <entry>
        <author>Іван Богданов</author>
        <date>2026-06-01 10:00</date>
        <message>Дуже крутий сайт! Зручно дивитися погоду.</message>
    </entry>
    <entry>
        <author>Admin</author>
        <date>2026-06-01 12:15</date>
        <message>Дякуємо за відгуки! Графіки будуть у наступному оновленні.</message>
    </entry>
</guestbook>
XML;
            file_put_contents($this->xmlFilePath, $defaultXml);
        }
    }

    public function startTag($parser, $tagName, $attrs): void
    {
        if ($tagName === 'ENTRY') {
            $this->htmlTable .= "<tr>";
        } elseif (in_array($tagName, ['AUTHOR', 'DATE', 'MESSAGE'])) {
            $this->htmlTable .= "<td>";
            $this->currentData = '';
        }
    }

    public function contents($parser, $data): void
    {
        $this->currentData .= $data;
    }

    public function endTag($parser, $tagName): void
    {
        if ($tagName === 'ENTRY') {
            $this->htmlTable .= "</tr>\n";
        } elseif (in_array($tagName, ['AUTHOR', 'DATE', 'MESSAGE'])) {
            $cleanText = htmlspecialchars(trim($this->currentData));
            $this->htmlTable .= "{$cleanText}</td>";
        }
    }

    private function parseXmlToHtml(): string
    {
        $this->htmlTable = <<<HTML
        <table class="table table-bordered table-striped table-hover mt-4 shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th scope="col" style="width: 20%;">Автор</th>
                    <th scope="col" style="width: 20%;">Дата</th>
                    <th scope="col" style="width: 60%;">Повідомлення</th>
                </tr>
            </thead>
            <tbody>
HTML;

        $parser = xml_parser_create();
        xml_set_element_handler($parser, [$this, "startTag"], [$this, "endTag"]);
        xml_set_character_data_handler($parser, [$this, "contents"]);
        $xmlData = file_get_contents($this->xmlFilePath);
        
        if (!xml_parse($parser, $xmlData, true)) {
            $errorLine = xml_get_current_line_number($parser);
            $errorString = xml_error_string(xml_get_error_code($parser));
            return "<div class='alert alert-danger'>Помилка XML парсера на рядку $errorLine: $errorString</div>";
        }

        unset($parser);

        $this->htmlTable .= "</tbody></table>";
        
        return $this->htmlTable;
    }

    public function get(): void
    {
        if (isset($_GET['success']) && $_GET['success'] == '1') {
            $this->message = "Ваш відгук успішно додано за допомогою DOMDocument!";
        }

        $tableHtml = $this->parseXmlToHtml();
        
        $errorHtml = $this->error ? "<div class='alert alert-danger'>{$this->error}</div>" : "";
        $messageHtml = $this->message ? "<div class='alert alert-success'>{$this->message}</div>" : "";

        $content = <<<HTML
        <section class="py-5">
            <div class="container">
                <h1 class="mb-4 text-center"><i class="bi bi-book text-success me-2"></i>Гостьова книга</h1>
                
                {$errorHtml}
                {$messageHtml}

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Записи (Згенеровано SAX-парсером)</h5>
                            </div>
                            <div class="card-body">
                                {$tableHtml}
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Залишити відгук (DOM)</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Ваше ім'я</label>
                                        <input type="text" class="form-control" id="author" name="author" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Повідомлення</label>
                                        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Відправити</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
HTML;

        $this->printBasePage($content);
    }

    public function post(): void
    {
        $author = htmlspecialchars(trim($_POST['author'] ?? 'Анонім'));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        $date = date('Y-m-d H:i');

        if (empty($message) || empty($author)) {
            $this->error = "Усі поля повинні бути заповнені!";
            $this->get();
            return;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $root = null;

        if (file_exists($this->xmlFilePath) && filesize($this->xmlFilePath) > 0) {
            $dom->load($this->xmlFilePath);
            $root = $dom->documentElement;
        } else {
            $root = $dom->createElement('guestbook');
            $dom->appendChild($root);
        }

        $entryNode = $dom->createElement('entry');
        
        $authorNode = $dom->createElement('author', $author);
        $dateNode = $dom->createElement('date', $date);
        $messageNode = $dom->createElement('message', $message);

        $entryNode->appendChild($authorNode);
        $entryNode->appendChild($dateNode);
        $entryNode->appendChild($messageNode);

        $root->appendChild($entryNode);

        if ($dom->save($this->xmlFilePath)) {
            header("Location: guestbook.php?success=1");
            exit();
        } else {
            $this->error = "Помилка збереження XML файлу.";
            $this->get(); 
        }
    }
}

$page = new GuestbookPage();
$page->render();