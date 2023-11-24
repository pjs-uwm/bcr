<?php

$dbc = connect();

function connect()
{
    // Connect to database
    $dbc = mysqli_connect("localhost", "pjsinger_bcr_app", "bcr_app2023", "pjsinger_brew_city");

    // Check connection
    if (!$dbc) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $dbc;
}

// Generic Query operation
function query($sql)
{
    $dbc = connect();
    $result = mysqli_query($dbc, $sql);
    return $result;
}

// Generic Query operation that returns array instead of mysqli result object
function query_arr($sql)
{
    $dbc = connect();

    $result = mysqli_query($dbc, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Generic Query operation that returns a boolean success / fail instead of mysqli result object
function query_bool($sql)
{
    $dbc = connect();
    if (mysqli_query($dbc, $sql)) {
        return true;
    } else {
        return false;
    }
}

// CREATE operation
function create($entity, $data)
{
    $dbc = connect();
    $fields = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $sql = "INSERT INTO $entity ($fields) VALUES ($values)";
    if (mysqli_query($dbc, $sql)) {
        return "Record created successfully!";
    } else {
        return "Error creating record: " . mysqli_error($dbc);
    }
}
function create_id_return($entity, $data)
{
    $dbc = connect();
    $fields = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_values($data)) . "'";
    $sql = "INSERT INTO $entity ($fields) VALUES ($values)";

    if (mysqli_query($dbc, $sql)) {
        return mysqli_insert_id($dbc);
    } else {
        return "Error creating record: " . mysqli_error($dbc);
    }
}

// READ operation
function read($entity, $id = "", $id_field = "")
{
    $dbc = connect();
    $sql = "SELECT * FROM $entity";
    if ($id) {
        $sql .= " WHERE $id_field = $id";
    }
    $result = mysqli_query($dbc, $sql);
    if (mysqli_num_rows($result) > 0) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        return NULL;
    }
}

// UPDATE operation
function update($entity, $id_col, $id, $data)
{
    $dbc = connect();
    $set_values = [];
    foreach ($data as $key => $value) {
        if ($value != "") {
            $set_values[] = "$key = '$value'";
        } else {
            $set_values[] = "$key = NULL";
        }
    }
    $set_values = implode(", ", $set_values);

    $sql = "UPDATE $entity SET $set_values WHERE $id_col = $id";

    if (mysqli_query($dbc, $sql)) {
        return true;
    } else {
        return false;
    }
}

// DELETE operation
function delete_entity($entity, $id_col, $id)
{
    $dbc = connect();
    $sql = "DELETE FROM $entity WHERE $id_col = $id";
    if (mysqli_query($dbc, $sql)) {
        return "Record deleted successfully!";
    } else {
        return "Error deleting record: " . mysqli_error($dbc);
    }
}


function generateWhereClause($conditions)
{
    $where = [];
    foreach ($conditions as $field => $value) {
        $where[] = $value[0] . " " . $value[2] . " " . $value[3] . "" . $value[1] . $value[3];
    }
    if ($conditions) {
        return "WHERE " . implode(" AND ", $where);
    } else {
        return '';
    }
}



