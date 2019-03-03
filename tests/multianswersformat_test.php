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
 * Unit tests for the Embedded answer (Cloze) question importer.
 *
 * @package   qformat_multianswers
 * @copyright 2018 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format.php');
require_once($CFG->dirroot . '/question/format/multianswers/format.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


/**
 * Unit tests for the Embedded answer (Cloze) question importer.
 *
 * @copyright 2012 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qformat_multianswers_test extends question_testcase {

    public function test_import() {
        $lines = file(__DIR__ . '/fixtures/questions.multianswers.txt');

        $importer = new qformat_multianswers();
        $qs = $importer->readquestions($lines);

        $expectedq = (object) array(
            'name' => 'Match the following cities with the correct state:
* San Francisco: {#1}
* ...',
            'questiontext' => 'Match the following cities with the correct state:
* San Francisco: {#1}
* Tucson: {#2}
* Los Angeles: {#3}
* Phoenix: {#4}
The capital of France is {#5}.
',
            'questiontextformat' => FORMAT_MOODLE,
            'generalfeedback' => '',
            'generalfeedbackformat' => FORMAT_MOODLE,
            'qtype' => 'multianswer',
            'defaultmark' => 5,
            'penalty' => 0.3333333,
            'length' => 1,
        );

        $this->assertEquals(4, count($qs));
        $this->assert(new question_check_specified_fields_expectation($expectedq), $qs[0]);

        $this->assertEquals('multichoice', $qs[0]->options->questions[1]->qtype);
        $this->assertEquals('multichoice', $qs[0]->options->questions[2]->qtype);
        $this->assertEquals('multichoice', $qs[0]->options->questions[3]->qtype);
        $this->assertEquals('multichoice', $qs[0]->options->questions[4]->qtype);
        $this->assertEquals('shortanswer', $qs[0]->options->questions[5]->qtype);
    }
    
    public function test_import_bad_questions() {
        $lines = file(__DIR__ . '/fixtures/bad.questions.multianswers.txt');

        $importer = new qformat_multianswers();

        // The importer echoes some errors, so we need to capture and check that.
        ob_start();
        $qs = $importer->readquestions($lines);
        $output = ob_get_contents();
        ob_end_clean();

        // Check that there were some expected errors.
        $this->assertContains('Error importing question', $output);
        $this->assertContains('Invalid embedded answers (Cloze) question', $output);
        $this->assertContains('This type of question requires at least 2 choices', $output);
        $this->assertContains('One of the answers should have a score of 100% so it is possible to get full marks for this question.',
                $output);
        $this->assertContains('The answer must be a number, for example -1.234 or 3e8, or \'*\'.', $output);
        $this->assertContains('The question text must include at least one embedded answer.', $output);
        $this->assertEquals(0, count($qs));
    }
}
