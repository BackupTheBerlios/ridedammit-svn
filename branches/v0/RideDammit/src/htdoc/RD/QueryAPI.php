<?php
/*
Copyright(c) 2003 Nathan P Sharp

This file is part of Ride Dammit!.

Ride Dammit! is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Ride Dammit! is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ride Dammit!; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//----------------------------------------------------------
// QueryAPI.php
//
// Utility for safely creating MySQL queries.
//
// For now, only builds "where" clauses.
//
// Every class in here extends the QueryObj base class and
// has a toString() method which prints out the safe MySQL
// statement.
//
//
// Usage:
// $query = new QueryBinaryOp(
//             new QueryColumnRef("Table.Column"),
//               "=",
//             new QueryIntLiteral(3);
//
// $query2 = new QuueryUnaryOp(
//             "!", $query);
// $bla = mysql_query("select * from Table where ". $query2->toString());
// ...


/**
 * The valid list of operators in MySQL.  This is certainly
 * incomplete right now.
 */
$QueryOperators = array(
   "and",
   "in",
   "or",
   "not",
   "!",
   "&&",
   "||",
   "like",
   "=",
   "<>",
   "<",
   "<=",
   ">",
   ">=");

/**
 * QueryObj is the base class upon which everything else in 
 * this package extends.  Use the "toString()" method to
 * print out the safe SQL statement for the current query.
 */
class QueryObj
{

   function QueryObj()
   {
   }

   /**
    * Prints out the safe SQL query.  Effectively a
    * pure virtual (base class does nothing).
    */
   function toString()
   {
      return "";
   }

   /**
    * Useful check utility which verifies that a
    * plain word is a valid MySQL word.  Until I go
    * read the documentation or find a need for anything
    * else, right now it requires that the input be a
    * string of alphanumeric or underscore.
    *
    * @return What was passed to it if the result is
    *   safe, an empty string if it is not.
    */
   function checkSQLWord($in)
   {
      if ( ! preg_match("/^[a-zA-Z_0-9]*$/", $in) )
      {
         $this->badInput("SQL Word Check", $in);
         return "";
      }
      return $in;
   }

   /**
    * Useful check utility which verifies that the
    * input is a valid MySQL operator.  See the 
    * list of operators at the top of this file.
    *
    * @return What was passed to it if the result is
    *   safe, an empty string if it is not.
    */
   function checkOperator($in)
   {
      global $QueryOperators;
      $in = strtolower($in);
      if ( ! in_array($in, $QueryOperators) )
      {
         $this->badInput("Bad Operator", $in);
         return "and";
      }
      return $in;
   }

   /** 
    * Utility function which logs to the error utility
    * when something bad is detected.
    */
   function badInput($where, $what)
   {
      error_log("WARNING: QueryAPI found invalid input!", 0);
      error_log("       -- $where : $what", 0);
   }
}

/**
 * A integral literal
 */
class QueryIntLiteral extends QueryObj
{
   var $val;

   function QueryIntLiteral($val)
   {
      $this->QueryObj();
      //NPS: could check this and log it if it is bad instead of
      //just coercing it.
      $this->val = (int)$val;
   }

   function toString()
   {
      return "".$this->val;
   }
}

/**
 * A integral literal
 */
class QueryFloatLiteral extends QueryObj
{
   var $val;

   function QueryIntLiteral($val)
   {
      $this->QueryObj();
      //NPS: could check this and log it if it is bad instead of
      //just coercing it.
      $this->val = (float)$val;
   }

   function toString()
   {
      return "".$this->val;
   }
}

/**
 * A string literal.  The string will be quoted and slashed
 * appropriately.
 */
class QueryStrLiteral extends QueryObj
{
   var $val;

   function QueryStrLiteral($val)
   {
      $this->QueryObj();
      $this->val = $val;
   }

   function toString()
   {
      return "\"".addslashes($this->val)."\"";
   }
}

/**
 * A list of integers (useful for 'xxx in (1,2,3)' type statements).
 */
class QueryIntListLiteral extends QueryObj
{
   var $vals;
   
   /** 
    * Pass in an array of integers
    */
   function QueryIntListLiteral($vals)
   {
      $this->QueryObj();
      $this->vals = array();
      foreach ( $vals as $val )
      {
         //NPS: could check this and log it if it is bad instead of
         //just coercing it.
         $this->vals[] = (int)$val;
      }
   }
   
   function toString()
   {
      return "( ".implode(", ", $this->vals)." )";
   }
}

/**
 * A reference to a column name.  For now my logic
 * is at most two words which pass the "checkSQLword()" check
 * and are joined by a period.
 */
class QueryColumnRef extends QueryObj
{
   var $ref;

   function QueryColumnRef($ref)
   {
      $this->ref = $this->checkColumnRef($ref);
   }

   function toString()
   {
      return $this->ref;
   }

   function checkColumnRef($ref)
   {
      $chunks = explode(".", $ref);
      if ( count($chunks) > 2 )
      {
         $this->badInput("columnRef", $ref);
         return "";
      }
      foreach ( $chunks as $chunk )
      {
         if ( $chunk != $this->checkSQLWord($chunk) )
         {
            $this->badInput("columnRefPart", $chunk);
            return "";
         }
      }
      //Passed
      return $ref;
   }
}

/**
 * A binary operator.  Consists of a left side,
 * operator, and a right side.  The left and
 * right sides must be QueryObj's themselves.
 */
class QueryBinaryOp extends QueryObj
{
   var $term1;
   var $op;
   var $term2;

   function QueryBinaryOp($term1, $op, $term2)
   {
      $this->QueryObj();
      $this->term1 = $term1;
      $this->op = $this->checkOperator($op);
      $this->term2 = $term2;
   }

   function toString()
   {
      return " ( " . $this->term1->toString() .
             " " . $this->op . " " .
             $this->term2->toString() . " ) ";
   }
}

/**
 * A unary operator.  Consists of an operator and a
 * right side.  The right sides must be a QueryObj.
 */
class QueryUnaryOp extends QueryObj
{
   var $op;
   var $term1;

   function QueryBinaryOp($op, $term1)
   {
      $this->QueryObj();
      $this->op = $this->checkOperator($op);
      $this->term1 = $term1;
   }

   function toString()
   {
      return " ( " .
             $this->op . " " .
             $this->term2->toString() . " ) ";
   }
}


?>
