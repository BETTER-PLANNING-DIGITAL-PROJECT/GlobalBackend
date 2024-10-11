<?php

namespace App\Controller\School\Exam\Mark;

use App\Controller\GlobalController;
use App\Entity\School\Exam\Operation\Mark;
use App\Entity\Security\User;
use App\Repository\School\Exam\Configuration\EvaluationPeriodRepository;
use App\Repository\School\Exam\Configuration\ExamInstitutionSettingsRepository;
use App\Repository\School\Exam\Configuration\NoteTypeRepository;
use App\Repository\School\Exam\Configuration\SequenceRepository;
use App\Repository\School\Exam\Operation\MarkRepository;
use App\Repository\School\Schooling\Configuration\SchoolClassRepository;
use App\Repository\School\Schooling\Configuration\SchoolRepository;
use App\Repository\School\Schooling\Registration\StudentRegistrationRepository;
use App\Repository\School\Schooling\Registration\StudentRepository;
use App\Repository\School\Study\Program\ClassProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[AsController]
class ExportMarkController extends AbstractController
{
    public function __construct(
        private readonly GlobalController                  $globalController,
        private readonly SchoolRepository                  $schoolRepository,
        private readonly SchoolClassRepository             $classRepository,
        private readonly ClassProgramRepository            $classProgramRepository,
        private readonly SequenceRepository                $sequenceRepository,
        private readonly EvaluationPeriodRepository        $evaluationPeriodRepository,
        private readonly NoteTypeRepository                $noteTypeRepository,
        private readonly StudentRegistrationRepository     $studentRegistrationRepository,
        private readonly StudentRepository                 $studentRepository,
        private readonly MarkRepository                    $markRepository,
        private readonly ExamInstitutionSettingsRepository $examInstitutionSettingsRepository
    )
    {
    }

    public function getUser(): User
    {
        return $this->globalController->getUser();
    }

    #[Route('/api/export/mark/by-class-program/{schoolMarkData}', name: 'export_mark_by_class_program')]
    public function __invoke(string $schoolMarkData): Response
    {
        $schoolMark = json_decode($schoolMarkData,true);

        // Traduction
        $locale = $schoolMark['locale'];

        $translationsArray = $this->getTranslations($locale);
        
        // Type d'exportation
        $exportationTypeDatas = $schoolMark['exportationTypeDatas'];
        $property = $exportationTypeDatas['property'];
        $columnTitle = $exportationTypeDatas['columnTitle'];


        // Notes
        $schoolMarkDatas = $this->getMarksByClassProgram($schoolMark);
        // On ne prends que une seule matiere
        $schoolMarks = $schoolMarkDatas['schoolMarks'][0];
//        dd($exportationTypeDatas);
        $datas = $schoolMarkDatas['datas'];

        // Creation et enregistrement du fichier Excel pour la matiere
        $spreadSheetDatas = $this->createSpreadsheet($schoolMarks, $datas, $translationsArray, $property, $columnTitle);
        $filePath = $this->saveSpreadsheet($spreadSheetDatas);
        $fileName = $spreadSheetDatas['fileName'];
        // Telechargement dans le navigateur du client

        $response = $this->download($filePath,$fileName);
        return ($response);
    }

    function download(string $filePath,string $fileName): BinaryFileResponse{
        /*$response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$fileName);
        return $response;*/

        return $this->file($filePath,$fileName);
    }

    function saveSpreadsheet(array $spreadSheetDatas){
        $spreadsheet = $spreadSheetDatas['spreadsheet'];
        $fileName = $spreadSheetDatas['fileName'];

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // In this case, we want to write the file in the public directory
        $publicDirectory = $this->getParameter('kernel.project_dir'). '/public/exported marks';
        // e.g /var/www/project/public/my_first_excel_symfony4.xlsx
        $excelFilepath =  $publicDirectory . '/'.$fileName;

        // Create the file
        $writer->save($excelFilepath);

        // Return a text response to the browser saying that the excel was succesfully created
        return $excelFilepath;
    }

    function createSpreadsheet(array $schoolMarks,array $datas,array $translationsArray,string $property,string $columnTitle):array
    {
        // Recuperation des elements pour l'en tete
        $class = $datas['class'];
        $sequence = $datas['sequence'];
        $noteType = $datas['noteType'];

        $year  = $class->getYear();
        $school  = $class->getSchool();

        $classPrograms = $datas['classPrograms'];

        $classProgram = $classPrograms[0];

        $evaluationPeriod = $classProgram->getEvaluationPeriod();

        // Creation de la feuille de calcul
        $spreadsheet = new Spreadsheet();
        // Get active sheet - it is also possible to retrieve a specific sheet
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($this->translate('mark',$translationsArray));

        // Affichage des criteres de filtre
            // Annee
        $sheet->setCellValue('A1', $this->translate('year',$translationsArray))->mergeCells('A1:B1');
        $sheet->setCellValue('C1', $year->getYear())->mergeCells('C1:D1');


            // Ecole
        $sheet->setCellValue('A2', $this->translate('school',$translationsArray))->mergeCells('A2:B2');
        $sheet->setCellValue('C2', $school->getCode())->mergeCells('C2:D2');

            // Classe
        $sheet->setCellValue('A3', $this->translate('class',$translationsArray))->mergeCells('A3:B3');
        $sheet->setCellValue('C3', $class->getCode())->mergeCells('C3:D3');

            // Periode d'evaluation
        $sheet->setCellValue('A4', $this->translate('evaluation period',$translationsArray))->mergeCells('A4:B4');
        $sheet->setCellValue('C4', $evaluationPeriod->getName())->mergeCells('C4:D4');

            // Sequence
        $sheet->setCellValue('A5', $this->translate('sequence',$translationsArray))->mergeCells('A5:B5');
        $sheet->setCellValue('C5', $sequence->getCode())->mergeCells('C5:D5');

            // Type de notes
        $sheet->setCellValue('A6', $this->translate('note type',$translationsArray))->mergeCells('A6:B6');
        $sheet->setCellValue('C6', $noteType?->getName())->mergeCells('C6:D6');

            // Matiere
        $sheet->setCellValue('A7', $this->translate('course',$translationsArray))->mergeCells('A7:B7');
        $sheet->setCellValue('C7', $classProgram->getNameuvc())->mergeCells('C7:D7');

        $schoolMark = $schoolMarks[0];

        $base = $schoolMark['base'];
        $assignmentDate = $schoolMark['assignmentDate'];
//        dd($assignmentDate);
        $weighting = $schoolMark['weighting'];
        $description = $schoolMark['description'];

        // La ligne vide avant la base
        $sheet->mergeCells('A8:B8');
        $sheet->mergeCells('C8:D8');

        $textAlign = function (string $coordinate,string $alignment = Alignment::HORIZONTAL_CENTER) use ($sheet){
            $sheet->getStyle($coordinate)->getAlignment()->setHorizontal($alignment);
        };

        // Base
        $sheet->setCellValue('A9',$this->translate('base',$translationsArray). '*')->mergeCells('A9:B9');
        $sheet->setCellValue('C9',$base)->mergeCells('C9:D9');
        $textAlign('C9',Alignment::HORIZONTAL_LEFT);

        // Regles de validation de la cellule de la base (>0)
        $validation = $sheet->getCell('C9')->getDataValidation();
        $validation->setType( DataValidation::TYPE_CUSTOM);
        $validation->setErrorStyle( DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setFormula1('AND(C9>0,ISNUMBER(C9))');
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle($this->translate('the base is not valid',$translationsArray));
        $validation->setError($this->translate('please enter a base > 0',$translationsArray));
        $validation->setPromptTitle($this->translate('mark entry base',$translationsArray));
        $validation->setPrompt($this->translate('enter a base > 0',$translationsArray));

            // Date du devoir
        $sheet->setCellValue('A10', $this->translate('date of assignment',$translationsArray))->mergeCells('A10:B10');

        $sheet->setCellValue('C10',$assignmentDate)->mergeCells('C10:D10');
        $textAlign('C10',Alignment::HORIZONTAL_LEFT);

        $validation = $sheet->getCell('C10')->getDataValidation();
        $validation->setType( DataValidation::TYPE_CUSTOM);
        $validation->setErrorStyle( DataValidation::STYLE_STOP);
        $validation->setAllowBlank( true);
//        $validation->setFormula1('RegExpMatch(C10,\b\d{4}-\d{2}-\d{2}\b)');
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle($this->translate('the date entered is not a valid date',$translationsArray));
        $validation->setError($this->translate('invalid date',$translationsArray));
        $validation->setPromptTitle($this->translate('date of assignment',$translationsArray));
        $validation->setPrompt($this->translate('enter a valid date',$translationsArray));

        // Description
        $sheet->setCellValue('A11', $this->translate('description',$translationsArray))->mergeCells('A11:B11');
        $sheet->setCellValue('C11',$description)->mergeCells('C11:D11');
        $textAlign('C11',Alignment::HORIZONTAL_LEFT);

            // Ponderation
        $sheet->setCellValue('A12', $this->translate('weighting',$translationsArray))->mergeCells('A12:B12');
        $sheet->setCellValue('C12',$weighting)->mergeCells('C12:D12');
        $textAlign('C12',Alignment::HORIZONTAL_LEFT);

            // Regles de validation de la cellule de la ponderation (>0)
        $validation = $sheet->getCell('C12')->getDataValidation();
        $validation->setType( DataValidation::TYPE_CUSTOM);
        $validation->setErrorStyle( DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setFormula1('AND(C12>0,ISNUMBER(C12))');
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle($this->translate('weighting is not valid',$translationsArray));
        $validation->setError($this->translate('please enter a weighting > 0',$translationsArray));
        $validation->setPromptTitle($this->translate('mark weighting',$translationsArray));
        $validation->setPrompt($this->translate('enter a weighting > 0',$translationsArray));

            // NB
        $sheet->setCellValue('A16', 'NB');

            // Les champs avec un * sont obligatoires
        $sheet->setCellValue('A17', $this->translate('fields marked with * are required',$translationsArray));

        // La partie des notes des etudiants
        $sheet->setCellValue('J1', '#*');
        $sheet->getStyle('J1')->getProtection()->setHidden(Protection::PROTECTION_PROTECTED);
        $textAlign('J1');
        $sheet->setCellValue('K1', $this->translate($columnTitle,$translationsArray))->mergeCells('K1:Q1');
        $textAlign('K1');
        $sheet->setCellValue('R1', $this->translate('mark',$translationsArray));
        $textAlign('R1');

//            dd($schoolMarks);

        // Verrouillage des cellules du fichier
        $sheet->getProtection()->setPassword('password hare');
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setSelectUnlockedCells(false);

        // Deverouiller une cellule (pr les notes)
        $unlockCell = function (string $coordinate) use ($sheet){
            $sheet->getStyle($coordinate)->getProtection()->setLocked(false);
        };

        foreach ($schoolMarks as $i=>$schoolMark) {
            $rowIndex = $i + 2;

            $jCell = 'J' . $rowIndex;
            $sheet->setCellValue($jCell,$schoolMark['id']);
            $sheet->getStyle($jCell)->getProtection()->setHidden(Protection::PROTECTION_PROTECTED);
            $textAlign($jCell);

            $kCell = 'K' . ($rowIndex);
            $sheet->setCellValue($kCell,$schoolMark[$property])->mergeCells($kCell.':Q'.($i+2));
            $textAlign($kCell);

            $rCell = 'R' . $rowIndex;
            $sheet->setCellValue($rCell,$schoolMark['mark']);
            $unlockCell($rCell);
            $textAlign($rCell);

            // Regles de validation de la cellule de la note (>=0 et < Base)
            $validation = $sheet->getCell($rCell)->getDataValidation();
            $validation->setType( DataValidation::TYPE_CUSTOM);
            $validation->setErrorStyle( DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setFormula1('AND('.$rCell.' >= 0,'.$rCell.' <= C9,ISNUMBER('.$rCell.'))');
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle($this->translate('mark is invalid',$translationsArray));
            $validation->setError($this->translate('please enter a mark >= 0 and lesser than the base',$translationsArray));
            $validation->setPromptTitle($this->translate('mark',$translationsArray));
            $validation->setPrompt($this->translate('enter a mark >= 0 and lesser than the base',$translationsArray));
        }

        // Cacher la colonne J qui a les identifiants des lignes de notes
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setCollapsed(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setVisible(false);

        // Deverouiller les cellules de Base,Ponderation etc...
        $unlockCell('C9');
        $unlockCell('C10');
        $unlockCell('C11');
        $unlockCell('C12');
        $unlockCell('C120');

        $fileName = $this->translate('marks of',$translationsArray).' '.$evaluationPeriod->getName().' '.$sequence->getCode().($noteType ? ' '.$noteType->getName() : ' NNT').' '.$classProgram->getNameuvc().' '.$class->getCode().' '.$year->getYear().'.xlsx';
        return ['spreadsheet'=>$spreadsheet,'fileName'=>$fileName];
    }

    /**
     * @param mixed $schoolMark
     * @return JsonResponse
     */
    public function getMarksByClassProgram(mixed $schoolMark): array
    {
        $classId = $schoolMark['classId'];
        $sequenceId = $schoolMark['sequenceId'];

        $class = $this->classRepository->find($classId);
        $sequence = $this->sequenceRepository->find($sequenceId);
        $noteType = isset($schoolMark['noteTypeId']) && $schoolMark['noteTypeId'] !== null ? $this->noteTypeRepository->find($schoolMark['noteTypeId']) : null;

        $institution = $sequence->getInstitution();

        $criteria = ['institution' => $institution, 'noteType' => $noteType, 'sequence' => $sequence];

        $evaluationPeriod = null;
        if (isset($schoolMark['evaluationPeriodId'])) {
            $evaluationPeriod = $this->evaluationPeriodRepository->find($schoolMark['evaluationPeriodId']);
            $criteria['evaluationPeriod'] = $evaluationPeriod;
        }

        $classPrograms = null;

        $schoolMarks = [];

        if (isset($schoolMark['classProgramIds'])) {
            $classProgramIds = $schoolMark['classProgramIds'];
        } else {
            $classProgramIds = $this->markRepository->findClassProgramIds($class, $evaluationPeriod, $sequence, $noteType);
            $classProgramIds = array_column($classProgramIds, 'classProgramId');
        }

        $classPrograms = array_map(fn(int $classProgramId) => $this->classProgramRepository->find($classProgramId), $classProgramIds);

        $examInstitutionSettings = $this->examInstitutionSettingsRepository->findOneBy(['institution' => $institution]);
        $hideNamesIfAnonymityAvailable = $examInstitutionSettings->getHideNamesIfAnonymityAvailable();
//        $schoolMarks = $this->markRepository->findBy($criteria);

        // hideNamesIfAnonymityAvailable = false | true (0 | 1)
        $formatter0 = function (Mark $schoolMark) {
            $student = $schoolMark->getStudent()->getStudent();
            $assignmentDate = $schoolMark->getAssignmentDate();
            $assignmentDate = isset($assignmentDate) ? date_format($assignmentDate, 'Y-m-d') : null;
            $classProgram = $schoolMark->getClassProgram();
            return [
                'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
                'name' => $student->getFirstName() . " " . $student->getName(),
                'fullName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'studentName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'markEntered' => $schoolMark->getMarkEntered(),
                'matricule' => $student->getMatricule(),
                'id' => $schoolMark->getId(),
                'anonymity code' => $schoolMark->getAnonymityCode(),
                'anonymityCode' => $schoolMark->getAnonymityCode(),
                'mark' => $schoolMark->getMark(),
                'base' => $schoolMark->getBase(),
                'weighting' => $schoolMark->getWeighting(),
                'assignmentDate' => $assignmentDate,
                'description' => $schoolMark->getDescription(),
                'subjectName' => $classProgram->getNameuvc(),
                'isSimulated' => $schoolMark->getIsSimulated(),
                'isOpen' => $schoolMark->isIsOpen()
            ];
        };
        $formatter1 = function (Mark $schoolMark) {
            $student = $schoolMark->getStudent()->getStudent();
            $assignmentDate = $schoolMark->getAssignmentDate();
            $assignmentDate = isset($assignmentDate) ? date_format($assignmentDate, 'Y-m-d') : null;
            $classProgram = $schoolMark->getClassProgram();

            $anonymityCode = $schoolMark->getAnonymityCode();
            $name = $student->getFirstName() . " " . $student->getName();
            $matricule = $student->getMatricule();

            $nameMatricule = $student->getName() . " " . $matricule;
            $fullName = $student->getFirstName() . " " . $nameMatricule;
            if ($anonymityCode) {
                $fullName = $name = $matricule = $anonymityCode;
            }
            return [
                'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
                'name' => $name,
                'fullName' => $fullName,
                'studentName' => $fullName,
                'markEntered' => $schoolMark->getMarkEntered(),
                'matricule' => $matricule,
                'id' => $schoolMark->getId(),
                'anonymity code' => $anonymityCode,
                'anonymityCode' => $anonymityCode,
                'mark' => $schoolMark->getMark(),
                'base' => $schoolMark->getBase(),
                'weighting' => $schoolMark->getWeighting(),
                'assignmentDate' => $assignmentDate,
                'description' => $schoolMark->getDescription(),
                'subjectName' => $classProgram->getNameuvc(),
                'isSimulated' => $schoolMark->getIsSimulated(),
                'isOpen' => $schoolMark->isIsOpen()];
        };
        /*$formatter0 = function (Mark $schoolMark) {
            $student = $schoolMark->getStudent()->getStudent();
            $assignmentDate = $schoolMark->getAssignmentDate();
            $assignmentDate = isset($assignmentDate) ? date_format($assignmentDate, 'Y-m-d') : null;
            $classProgram = $schoolMark->getClassProgram();
            return [
                'evaluationPeriodName' => $classProgram->getEvaluationPeriod()->getName(),
                'name' => $student->getFirstName() . " " . $student->getName(),
                'fullName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'studentName' => $student->getFirstName() . " " . $student->getName() . " " . $student->getMatricule(),
                'markEntered' => $schoolMark->getMarkEntered(),
                'matricule' => $student->getMatricule(),
                'id' => $schoolMark->getId(),
                'anonymity code' => $schoolMark->getAnonymityCode(),
                'anonymityCode' => $schoolMark->getAnonymityCode(),
                'mark' => $schoolMark->getMark(),
                'base' => $schoolMark->getBase(),
                'assignmentDate' => $assignmentDate,
                'description' => $schoolMark->getDescription(),
                'subjectName' => $classProgram->getNameuvc(),
                'isSimulated' => $schoolMark->getIsSimulated(),
                'isOpen' => $schoolMark->isIsOpen()];
        };*/

        foreach ($classPrograms as $classProgram) {
            $criteria['classProgram'] = $classProgram;
            $classProgramSchoolMarks = $this->markRepository->findBy($criteria);
            $schoolMarks[] = array_map(${'formatter' . (int)$hideNamesIfAnonymityAvailable}, $classProgramSchoolMarks);
        }

        return ['schoolMarks'=>$schoolMarks,
            'datas'=>[
                'class'=>$class,
                'noteType'=>$noteType,
                'sequence'=>$sequence,
                'classPrograms'=>$classPrograms,
            ]
        ];
    }

    private function getTranslations(string $locale)
    {
        $data = file_get_contents('assets/i18n/'.$locale.'.json');

        $translations = json_decode($data, true);

        return $translations;
    }

    private function translate(string $key, array $translations){
        return array_key_exists($key, $translations) ? $translations[$key] : $key;
    }
}