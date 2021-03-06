<?php

require_once("model/Izdelek_B.php");
require_once("model/Ocena_B.php");
require_once("ViewHelper.php");


class IzdelkiController
{

    public static function index()
    {
        $data = filter_input_array(INPUT_GET);

        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            // Iskanje
            //$search = filter_input_array(INPUT_POST);
            $search = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $izdelki = Izdelek_B::iskanje($search);
            foreach ($izdelki as &$izdelek) {
                $izdelek["slike"] = Slika_B::get($izdelek);
            }
            echo ViewHelper::render("view/izdelek-list.php", [
                "izdelki" => $izdelki
            ]);
        }


        if ($data["id"]) {
            $izdelek = Izdelek_B::get($data);
            $izdelek["slike"] = Slika_B::get($izdelek);
            echo ViewHelper::render("view/izdelek-detail.php", [
                "izdelek" => $izdelek,
                "ocene" => Ocena_B::get(["izdelek_id" => $data["id"]])
            ]);
        } else {
            echo "nesss";
            $izdelki = Izdelek_B::getAktivni();
            foreach ($izdelki as &$izdelek) {
                $izdelek["slike"] = Slika_B::get(["izdelek_id" => $izdelek["idIzdelek"]]);
            }
            echo ViewHelper::render("view/izdelek-list.php", [
                "izdelki" => $izdelki
            ]);
        }
    }

    public static function oceni()
    {
        if (!isset($_SESSION["idUporabnik"])) {
            header("Location:" . BASE_URL . "login");
            exit;
        }
        $validationRules = [
            'id' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1]
            ],
            'ocena' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, "max_range" => 5]
            ]
        ];
        $data = filter_input_array(INPUT_POST, $validationRules);
        try {
            $izdelek = Izdelek_B::get($data); // Izdelek mora obstajati
            Ocena_B::insertOrUpdate([
                    "uporabnik_id" => $_SESSION["idUporabnik"],
                    "izdelek_id" => $data["id"],
                    "ocena" => $data["ocena"]
                ]
            );
            header("Location:" . BASE_URL . "store?id=" . $data["id"]);
        } catch (Exception $e) {
            die($e->getMessage());
        }

    }

    public static function rest()
    {
        header('Content-Type: application/json');
        $izdelki = Izdelek_B::getAll();
        foreach ($izdelki as $_ => &$izdelek) {
            $izdelek["slike"] = Slika_B::get($izdelek); //vrne slike za izdelek
        }
        echo json_encode($izdelki);
    }


    public static function add()
    {
        if (!isset($_SESSION["idUporabnik"])) {
            header("Location:" . BASE_URL . "login");
            exit;
        }
        $form = new BooksInsertForm("add_form");

        if ($form->isSubmitted() && $form->validate()) {
            $id = BookDB::insert($form->getValue());
            ViewHelper::redirect(BASE_URL . "books?id=" . $id);
        } else {
            echo ViewHelper::render("view/book-form.php", [
                "title" => "Add book",
                "form" => $form
            ]);
        }
    }

    public static function edit()
    {
        if (!isset($_SESSION["idUporabnik"])) {
            header("Location:" . BASE_URL . "login");
            exit;
        }
        $editForm = new BooksEditForm("edit_form");
        $deleteForm = new BooksDeleteForm("delete_form");

        if ($editForm->isSubmitted()) {
            if ($editForm->validate()) {
                $data = $editForm->getValue();
                BookDB::update($data);
                ViewHelper::redirect(BASE_URL . "books?id=" . $data["id"]);
            } else {
                echo ViewHelper::render("view/book-form.php", [
                    "title" => "Edit book",
                    "form" => $editForm,
                    "deleteForm" => $deleteForm
                ]);
            }
        } else {
            $rules = [
                "id" => [
                    'filter' => FILTER_VALIDATE_INT,
                    'options' => ['min_range' => 1]
                ]
            ];

            $data = filter_input_array(INPUT_GET, $rules);

            if ($data["id"]) {
                $book = BookDB::get($data);
                $dataSource = new HTML_QuickForm2_DataSource_Array($book);
                $editForm->addDataSource($dataSource);
                $deleteForm->addDataSource($dataSource);

                echo ViewHelper::render("view/book-form.php", [
                    "title" => "Edit book",
                    "form" => $editForm,
                    "deleteForm" => $deleteForm
                ]);
            } else {
                throw new InvalidArgumentException("editing nonexistent entry");
            }
        }
    }

}
