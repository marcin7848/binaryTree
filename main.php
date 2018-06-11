<?php

class main
{
    private $html;

    public function __construct()
    {
        $this->html = file_get_contents("./view/main.xhtml");
        $this->controller();

        $this->ShowAdmin();

        $this->ShowMain();
        echo $this->html;
    }

    private function controller()
    {
        if($this->checkAdmin()) {
            if (isset($_GET['move'])) {
                if (isset($_POST['move_access'])) {
                    $this->moveNode($_POST['id'], $_POST['move_id'], $_POST['set_pos_under'], $_POST['value']);
                }
            }
            if (isset($_GET['sort'])) {
                $this->SortNode_Do($_POST['parent_id']);
            }
            if (isset($_POST['edit_node'])) {
                $this->EditNode();
            }
            if (isset($_POST['logout'])) {
                $this->LogOut();
            }
        }
        else{
            if (isset($_POST['log_in'])) {
                $this->LogIn($_POST['login'], $_POST['password']);
            }
        }




    }

    private function ShowMain()
    {
        $tab = $this->getBranch();

        $thisTree = "<ul>";

        $this->createTree($tab, $thisTree);

        $thisTree .= "</ul>";

        $thisTree .= "<div class='deletediv' id='-1'><span id='deletespan'></span>Przesuń tutaj, aby usunąć!</div>";
        $thisTree .= "<br /><div class='deletediv' id='-2'><span id='deletespan'></span>Przesuń tutaj,by edytować</div>";
        $thisTree .= "<br /><div class='sortdiv' id='sort_0' value='0'><span id='sortspan'></span>Posortuj główne węzły!</div>";


        $this->html = str_replace("<!--TREE-->", $thisTree, $this->html);
    }

    private function getBranch($id_parent = 0)
    {
        global $db;

        $query = "SELECT * FROM tree WHERE parent='" . $id_parent . "'";
        $query = $db->getQuery($query);

        $tabOfID = [];
        $tabOfPosition = [];
        $tabOfValue = [];

        while ($result = $query->fetch(PDO::FETCH_BOTH)) {
            $tabOfID = array_merge($tabOfID, array($result['id']));
            $tabOfPosition = array_merge($tabOfPosition, array($result['position']));
            $tabOfValue = array_merge($tabOfValue, array($result['value']));
        }

        array_multisort($tabOfPosition, $tabOfID, $tabOfValue);


        return array($tabOfID, $tabOfPosition, $tabOfValue);

    }

    private function createTree($tab, &$thisTree)
    {
        global $db;
        for ($i = 0; !empty($tab[0][$i]); $i++) {


            $query = "SELECT * FROM tree WHERE parent='" . $tab[0][$i] . "'";
            $query = $db->getQuery($query);
            if ($query->fetch(PDO::FETCH_BOTH)) {

                $thisTree .= "<div class='par'>";
                $thisTree .= "<div class='plus' id='mo_" . $tab[0][$i] . "'>+</div>";

                $thisTree .= "<li class='movenode' id='" . $tab[0][$i] . "' value='".$tab[2][$i]."'>" . $tab[2][$i] . "</li>";
                $thisTree .= "<span id='sort_" . $tab[0][$i] . "' value='" . $tab[0][$i] . "'>[S]</span>";
                $thisTree .= "</div>";


                $thisTree .= "<ul class='hide' id='tg_mo_" . $tab[0][$i] . "'>";

                $tab2 = $this->getBranch($tab[0][$i]);

                $this->createTree($tab2, $thisTree);

                $thisTree .= "</ul>";
            } else {
                $thisTree .= "<li class='movenode' id='" . $tab[0][$i] . "' value='".$tab[2][$i]."'>" . $tab[2][$i]."</li>";
            }
        }
    }

    private function ChangePosition($id, &$pos_blocked)
    {
        global $db;

        $query = "SELECT * FROM tree WHERE parent='" . $id . "'";
        $query = $db->getQuery($query);
        $tab = [];
        while ($wynik = $query->fetch(PDO::FETCH_BOTH)) {
            $tab += array($wynik['position']);
            if ($wynik['position'] >= $pos_blocked) {
                $query2 = "UPDATE tree SET position=position+1 WHERE id='" . $wynik['id'] . "'";
                $db->getQuery($query2);
            }
        }


        if (max($tab) < $pos_blocked) {
            $pos_blocked = max($tab) + 1;
        }

    }

    private function moveNode($id, $move_id, $set_pos_under, $value)
    {
        global $db;

            $add = 0;
            if ($id == "value_node_add") {
                $query = "INSERT INTO tree (`value`,`parent`,`position`) VALUES ('" . $value . "', '0','1')";
                $db->getQuery($query);
                $query = "SELECT * FROM tree WHERE value='" . $value . "' ORDER BY id DESC";
                $query = $db->getQuery($query);
                $result = $query->fetch(PDO::FETCH_BOTH);
                $id = $result['id'];
                $add = 1;
            } else {
                $query = "SELECT * FROM tree WHERE id='" . $id . "'";
                $query = $db->getQuery($query);
                $result = $query->fetch(PDO::FETCH_BOTH);
                $parent_id = $result['parent'];
                $position = $result['position'];

                $this->decrementPosition($parent_id, $position);
            }

            if ($move_id > 0) {
                $query = "SELECT * FROM tree WHERE id='" . $move_id . "'";
                $query = $db->getQuery($query);
                $result = $query->fetch(PDO::FETCH_BOTH);
                $parent_id_move = $result['parent'];
                $position_move = $result['position'];


                if ($set_pos_under == 1) {
                    $position_move++;
                }

                if ($add == 1) {
                    $parent_id_move = $move_id;
                    $query = "SELECT * FROM tree WHERE parent='" . $move_id . "' ORDER BY position DESC";
                    $query = $db->getQuery($query);
                    $result = $query->fetch(PDO::FETCH_BOTH);
                    $position_move = $result['position'];
                    $position_move++;
                }

                $position_move2 = $position_move;

                $this->ChangePosition($parent_id_move, $position_move);

                $query = "UPDATE tree SET parent='" . $parent_id_move . "', position='" . $position_move2 . "' WHERE id='" . $id . "'";
                $db->getQuery($query);

            } else if ($move_id == 0) {
                if ($add == 1) {
                    $parent_id_move = $move_id;
                    $query = "SELECT * FROM tree WHERE parent='" . $move_id . "' ORDER BY position DESC";
                    $query = $db->getQuery($query);
                    $result = $query->fetch(PDO::FETCH_BOTH);
                    $position_move = $result['position'];
                    $position_move++;

                    $position_move2 = $position_move;

                    $this->ChangePosition($parent_id_move, $position_move);

                    $query = "UPDATE tree SET parent='" . $parent_id_move . "', position='" . $position_move2 . "' WHERE id='" . $id . "'";
                    $db->getQuery($query);
                }
            } else if ($move_id == -1) {
                if ($add == 0) {
                    $query = "SELECT * FROM tree WHERE id='" . $id . "'";
                    $query = $db->getQuery($query);
                    $result = $query->fetch(PDO::FETCH_BOTH);
                    $parent = $result['parent'];

                    $query = "SELECT * FROM tree WHERE parent='" . $parent . "' ORDER BY position DESC";
                    $query = $db->getQuery($query);
                    $result = $query->fetch(PDO::FETCH_BOTH);
                    $last_position = $result['position'];


                    $query = "SELECT * FROM tree WHERE parent='" . $id . "'";
                    $query = $db->getQuery($query);
                    while($result = $query->fetch(PDO::FETCH_BOTH))
                    {
                        $last_position++;
                        $query2 = "UPDATE tree SET parent='" . $parent . "', position='" . $last_position . "' WHERE id='" . $result['id'] . "'";
                        $db->getQuery($query2);
                    }

                    $query = "DELETE FROM tree WHERE id='" . $id . "'";
                    $db->getQuery($query);
                }
            }

    }

    private function decrementPosition($id, $pos_del)
    {
        global $db;
        $query = "SELECT * FROM tree WHERE parent='" . $id . "'";
        $query = $db->getQuery($query);
        while ($result = $query->fetch(PDO::FETCH_BOTH)) {
            if ($result['position'] > $pos_del) {
                $query2 = "UPDATE tree SET position=position-1 WHERE id='" . $result['id'] . "'";
                $db->getQuery($query2);
            }
        }

    }

    private function EditNode()
    {
        global $db;
        if (!empty($_POST['id']) AND !empty($_POST['value'])) {
            if($this->validateValue($_POST['value'])) {
                $id = $_POST['id'];
                $value = $_POST['value'];

                $query = "UPDATE tree SET value='" . $value . "' WHERE id='" . $id . "'";
                $db->getQuery($query);
            }
            else{
                $this->html = str_replace("<!--ERROR-->", "Niedozwolone znaki!", $this->html);
            }
        }
    }

    private function SortNode($parent_id)
    {
        global $db;
        $query = "SELECT * FROM tree WHERE parent='" . $parent_id . "'";
        $query = $db->getQuery($query);

        $tabOfID = [];
        $tabOfValue = [];

        while ($result = $query->fetch(PDO::FETCH_BOTH)) {
            $tabOfID = array_merge($tabOfID, array($result['id']));
            $tabOfValue = array_merge($tabOfValue, array($result['value']));
        }

        array_multisort($tabOfValue, $tabOfID);

        return array($tabOfID, $tabOfValue);

    }

    private function SortNode_Do($parent_id)
    {
        global $db;

        $child = $this->SortNode($parent_id);

        for ($i = 0; $i < count($child[0]); $i++) {
            $query = "UPDATE tree SET position=" . $i . "+1 WHERE id='" . $child[0][$i] . "'";
            $db->getQuery($query);
        }
    }

    private function validateValue($value)
    {
        if(preg_match("/^[a-zA-Z0-9]+$/", $value))
            return 1;

        return 0;
    }

    private function ShowAdmin()
    {

        if($this->checkAdmin()) {
            $html = '<form action="index.php" method="POST">
            <input type="submit" name="logout" value="Wyloguj">
            </form>
            <br />
            Dodaj węzeł: <br/>
            <br/>Wartość węzła: <input type="text" id="add_value" name="value" maxlength="30" size="20"><br/>
            <input type="submit" id="add_node2" name="add_node" value="Dodaj">
            <div id="error_add"></div>
            <div class=\'moveadd\' id="value_node_add"></div>
        
            <br/>
        
            <div class="editshow">
                <br/>Edytuj węzeł: <br/>
                <form action="index.php" method="POST">
                    <input type="hidden" id="id_newvalue" name="id" value="">
                    Nowa wartość węzła: <input type="text" id="newvalue" name="value" maxlength="30" size="20"><br/>
                    <input type="submit" name="edit_node" value="Edytuj">
                    <div id="error_edit"></div>
                </form>
            </div>
            ';
        }
        else{
            $html = 'Logowanie <br />
            <form action="index.php" method="POST">
            <input type="text" id="login" name="login" maxlength="30" size="20">
            <input type="password" id="password" name="password" maxlength="30" size="20">
            <input type="submit" name="log_in" value="Zaloguj">
            <div id="error_login"></div>
            </form> 
            ';
        }

        $this->html = str_replace("<!--ADMIN-->", $html, $this->html);
    }

    private function LogIn($login, $password)
    {
        global $db;
        $login = htmlspecialchars(addslashes($login));
        $password = md5(htmlspecialchars(addslashes($password)));

        $query = "SELECT * FROM accounts WHERE login='" . $login . "' AND password = '".$password."'";
        $query = $db->getQuery($query);
        if ($result = $query->fetch(PDO::FETCH_BOTH)) {
            $_SESSION['login'] = $login;
            $_SESSION['password'] = $password;
            $this->html = str_replace("<!--ERROR-->", "Logowanie powiodło się!", $this->html);
            header('Refresh: 1;');
        }
        else{
            $this->html = str_replace("<!--ERROR-->", "Logowanie nie powiodło się!", $this->html);
        }
    }

    private function checkAdmin()
    {
        global $db;
        if(!isset($_SESSION['login']) OR !isset($_SESSION['password']))
            return 0;

        $login = htmlspecialchars(addslashes($_SESSION['login']));
        $password = htmlspecialchars(addslashes($_SESSION['password']));

        if(empty($login) OR empty($password))
            return 0;

        $query = "SELECT * FROM accounts WHERE login='" . $login . "' AND password = '".$password."'";
        $query = $db->getQuery($query);
        if (!$result = $query->fetch(PDO::FETCH_BOTH)) {
            return 0;
        }

        return 1;
    }

    private function LogOut()
    {
        session_destroy();
        $this->html = str_replace("<!--ERROR-->", "Wylogowano!", $this->html);
        header('Refresh: 1;');
    }

}
