<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Miltiple embedded answer (Cloze) questions importer.
 *
 * @package   qformat_multianswers
 * @copyright 2013 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Importer that imports a text file containing one or several multianswer questions
 * separated by //NEWCLOZEQUESTION
 * from a text file.
 *
 * @copyright 2013 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_multianswers extends qformat_default {

    public function provide_import() {
        return true;
    }

    public function readquestions($lines) {
        // Parses an array of lines into an array of questions.
        // using the function declared in the multianswer question type
        // a line containing '//NEWCLOZEQUESTION') is used as a separator
        // between the multianswer questions.
        question_bank::get_qtype('multianswer'); // Ensure the multianswer code is loaded.

        $questions = array();
        $newlines = array();
        for ($key = 0; $key < count($lines); $key++) {
            if (strpos($lines[$key], '//NEWCLOZEQUESTION') !== 0) {
                $newlines[] = $lines[$key];
            } else {
                $questiontext = array();
                $questiontext['text'] = implode('', $newlines);
                $questiontext['format'] = FORMAT_MOODLE;
                $questiontext['itemid'] = '';
                $question = qtype_multianswer_extract_question($questiontext);
                $errors = qtype_multianswer_validate_question($question);
                if ($errors) {
                    $this->error(get_string('invalidmultianswerquestion', 'qtype_multianswer', implode(' ', $errors)));
                } else {
                $question->questiontext = $question->questiontext['text'];
                $question->questiontextformat = FORMAT_MOODLE;
                $question->qtype = 'multianswer';
                $question->generalfeedback = '';
                $question->generalfeedbackformat = FORMAT_MOODLE;
                $question->length = 1;
                $question->penalty = 0.3333333;
                    $question->name = $this->create_default_question_name($question->questiontext, get_string('questionname', 'question'));
                    $questions[] = $question;
                }
                $newlines = array();
            }
        }
        // In case there is a single question or a question after the last  '//NEWCLOZEQUESTION'.
        if (!empty($newlines)) {
            $questiontext = array();
            $questiontext['text'] = implode('', $newlines);
            $questiontext['format'] = FORMAT_MOODLE;
            $questiontext['itemid'] = '';
            $question = qtype_multianswer_extract_question($questiontext);
            $errors = qtype_multianswer_validate_question($question);
            if ($errors) {
                $this->error(get_string('invalidmultianswerquestion', 'qtype_multianswer', implode(' ', $errors)));
            } else {
            $question->questiontext = $question->questiontext['text'];
            $question->questiontextformat = FORMAT_MOODLE;
            $question->qtype = 'multianswer';
            $question->generalfeedback = '';
            $question->generalfeedbackformat = FORMAT_MOODLE;
            $question->length = 1;
            $question->penalty = 0.3333333;
                $question->name = $this->create_default_question_name($question->questiontext, get_string('questionname', 'question'));
                $questions[] = $question;
            }
        }
        return $questions;
    }
}
