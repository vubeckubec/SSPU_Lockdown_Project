<?php
declare(strict_types = 1);
namespace App\Presenters;
use App\Model;
use Nette\Application\UI;
use Nette\Utils\DateTime;
use Tracy\Debugger;

final class LiteraturaPresenter extends BasePresenter {

    private $literaturaManager;

    public function __construct(Model\LiteraturaManager $literaturaManager) {
        $this->literaturaManager = $literaturaManager;
    }

    public function renderList($order = 'year DESC'): void {
        $this->template->literaturaList = $this->literaturaManager->getAll($order);
    }

    public function renderDetail($id): void {
        $this->template->literatura = $this->literaturaManager->getById($id);
    }

    public function actionInsert(): void {
        $today = new DateTime();
        $this['literaturaForm']['year']->setDefaultValue($today->format('Y'));
        $this['literaturaForm']['stars']->setDefaultValue('3');
        $this['literaturaForm']['category']->setDefaultValue('drama');
    }

    public function actionUpdate($id): void {
        $data = $this->literaturaManager->getById($id)->toArray();
        $this['literaturaForm']->setDefaults($data);
    }

    public function actionDelete($id): void {
        if ($this->literaturaManager->delete($id)) {
            $this->flashMessage('Záznam byl úspěšně smazán', 'success');
        } else {
            $this->flashMessage('Došlo k nějaké chybě při mazání záznamu', 'danger');
        }
        $this->redirect('list');
    }

    protected function createComponentLiteraturaForm(): UI\Form {
        $form = new UI\Form;

        $form->addText('title', 'Název díla:')
                ->addRule(UI\Form::PATTERN, 'Musí obsahovat aspoň 5 znaků', '.{5,}')
                ->setRequired(true);

        $form->addText('author', 'Autor:')
                ->addRule(UI\Form::MAX_LENGTH, 'Jméno nesmí být delší než %d znaků',50)
                ->setRequired(true);

        $form->addTextArea('anotation', 'Stručná charakteristika díla:')
                ->setHtmlAttribute('rows', '6')
                ->setRequired(true);

        $form->addText('year','Rok vzniku:')
             ->addRule(UI\Form::INTEGER, 'Rok musí být číslo')
             ->addRule(UI\Form::RANGE, 'Rok musí být od 1000 do 9999',[1000,9999]);        

        $kategorie = [
            'drama' => ' drama',
            'poezie' => ' poezie',
            'próza' => ' próza'
        ];
        $form->addRadioList('category', 'Literární druh:', $kategorie);

        $form->addText('stars', 'Hodnocení:')
                ->setHtmlType('number')
                ->setHtmlAttribute('min', '1')
                ->setHtmlAttribute('max', '5')
                ->setHtmlAttribute('step', '1')
                ->setHtmlAttribute('title', 'Zadejte hodnocení v rozsahu 1 až 5')
                ->addRule(UI\Form::RANGE, 'Hodnocení musí být v rozsahu od 1 do 5', [1, 5]);

        $form->addSubmit('submit', 'Potvrdit');

        $form->onSuccess[] = [$this, 'literaturaFormSucceeded'];
        return $form;
    }

    public function literaturaFormSucceeded(UI\Form $form, $values): void {
        $literaturaID = $this->getParameter('id');
        if ($literaturaID) {
            $akce = $this->literaturaManager->update($literaturaID, $values);
        } else {
            $akce = $this->literaturaManager->insert($values);
        }
        if ($akce) {
            $this->flashMessage('Kniha byla úspěšně uložena', 'success');
        } else {
            $this->flashMessage('Došlo k nějaké chybě při ukládání do databáze', 'danger');
        }
        $this->redirect('Literatura:list');
    }
}
