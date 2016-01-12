# ConsoleTable

Generates a table string ready to be printed in the console.

### Usage:

This sample code

    include_once 'ConsoleTable.php';

    // Create the table object
    $table = new ConsoleTable();

    // Set up the table headers
    $table->setHeaders(array(
        'name'     => 'Name',
        'director' => 'Director',
        'year'     => 'Year'
    ));

    // Set the table data massively
    $table->setData(array(
        array('name' => 'Star Wars: Episode IV - A New Hope', 'director' => 'George Lucas',     'year' => '1977'),
        array('name' => 'Back to the Future',                 'director' => 'Robert Zemeckis',  'year' => '1985'),
        array('name' => 'Los Colimbas se Divierten',          'director' => 'Enrique Carreras', 'year' => '1986')
    ));

    // Add an additional row to the end of the table
    $table->addRow(array(
        'name'     => 'Matilda',
        'director' => 'Danny DeVito',
        'year'     => '1996'
    ));

    // Print as a table in the console
    echo $table->toString();

will print:

    +------------------------------------+------------------+------+
    | Name                               | Director         | Year |
    +------------------------------------+------------------+------+
    | Star Wars: Episode IV - A New Hope | George Lucas     | 1977 |
    | Back to the Future                 | Robert Zemeckis  | 1985 |
    | Los Colimbas se Divierten          | Enrique Carreras | 1986 |
    | Matilda                            | Danny DeVito     | 1996 |
    +------------------------------------+------------------+------+

