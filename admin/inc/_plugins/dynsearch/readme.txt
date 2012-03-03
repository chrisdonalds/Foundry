DYNAMIC SEARCH
Web Template 3.0
Chris Donalds <chrisd@navigatormm.com>
========================================
Creates SQL criteria clause from Google(r)-type search phrasing

i.e:    word1 word2 --------------- returns results that include either 'word1' or 'word2'
        word1 or word2 ------------ returns results that include either 'word1' or 'word2'
        word1 and word2 ----------- returns results that include both 'word1' and 'word2'
        word1 not word2 ----------- returns results that include 'word1' but not 'word2'
        "abc 123" word3 ----------- returns results that include either 'abc 123' or 'word3'
        "abc 123" and word3 ------- returns results that include both 'abc 123' and 'word3'
        "abc 123" not word3 ------- returns results that include 'abc 123' but not 'word3'
        "abc 123" or not word3 ---- returns results that includes 'abc 123' or does not include 'word3'

-- Inclusions --
dynsearch

-- Implementation --
Input: 
    $searchtext (string):
            the entered search value
    $fieldarray (array):
            "text" => all text-based search fields
            "date" => all date-based search fields
            "time" => all time-based search fields
            "number" => all number-based search fields
            "binary" => all binary-based search fields
            "dollar" => all monetary-based fields

Output:
    on success: sql WHERE value
    on failure: false

Code Examples:

    if($search_text != "" && strtolower($search_text) != "search ..."){
        // call dosearch to create SQL WHERE string
        $search_clause = dosearch($search_text,
                                  array("text" => array("d.text_field1"{, "d.text_field2"}...),
                                        "number" => array("a.number_field1"{, "a.number_field2"}...),
                                        "date" => array("d.date1"{, "d.date2"}...),
                                        "time" => array("d.time1"{, "d.time2"}...),
                                        "dollar" => array("d.dollar_field1"{, "dollar_field2"}...)
                                       )
                                 );
        // append returned string to current WHERE clause
        if($search_clause !== false)
            $where_clause .= " AND ".$search_clause;
    }

    // prepare queries (number of rows and records)
    // getRecArrayJoin(array(tables,
                             fields,
                             joincriteria,
                             jointype,
                             $where_clause,
                             sortby,
                             limits,
                             groupby/having);

    $num  = getRecArrayJoinNumRows(array("table1 as d"{, "table2 as c"}...),
                          array("d.fields1"{, "c.fields2"}...),
                          array(""{, "d.field_id1 = c.field_id2"}...),
                          array(""{, "LEFT JOIN|RIGHT JOIN|INNER JOIN|JOIN"}...),
                          $where_clause);
    $recs = getRecArrayJoin(array("table1 as d"{, "table2 as c"}...),
                          array("d.fields1"{, "c.fields2"}...),
                          array(""{, "d.field_id1 = c.field_id2"}...),
                          array(""{, "LEFT JOIN|RIGHT JOIN|INNER JOIN|JOIN"}...),
                          $where_clause, $sort_by." ".$sort_dir, "$offset, $limit", "");
