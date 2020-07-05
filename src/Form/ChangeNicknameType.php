<?php

declare(strict_types=1);

namespace App\Form;

use MsgPhp\User\Infrastructure\Validator\UniqueUsername;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ChangeNicknameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('nickname', TextType::class, [
            'label' => 'label.username',
            'constraints' => [new NotBlank(), new UniqueUsername()],
        ]);
    }
}
