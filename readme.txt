���ݒ荀�ڈꗗ
  �Epath => �t�@�C���ۑ��ꏊ�ւ̃p�X
            ���������݌���������΁A���݂��Ȃ��p�X�͎����I�ɍ쐬����܂��B
              ��j
                'image1' => array('path'=>'/image/aaa/bbb')
                bbb�̃f�B���N�g�����Ȃ���Ԃ�
                /webroot/aaa�ɏ������݌����������bbb�f�B���N�g�����쐬����bbb�ɉ摜���z�u����܂��B
                ���ɘ_webroot�ɏ������݌���������΁A�f�B���N�g���͎����ō쐬����K�v�͂Ȃ��ł�����

  �Eresize => �摜�̃��T�C�Y�ݒ�
              �����T�C�Y����ɏc�T�C�Y�����T�C�Y���܂��B
                ��j
                  'image1' => array('path'=>'/image/aaa/bbb', 'resize' => 640)

  �Edelete => �A�b�v���[�h�����t�@�C���̍폜�������s�����̃t���O
              true : �폜����(�f�t�H���g)
              false : �폜���Ȃ�
                ��j�ҏW��ʂŁudelete_�J�������v�̃`�F�b�N�{�b�N�X��݂���ƃt�@�C���폜���s���܂��B(��DB�ɃJ�����͕K�v����܂���B)
                  // image1�̒l��null�ɂ��āA�t�@�C�����폜
                  'image1' => array('path'=>'/image/aaa/bbb', 'delete' => true)
                  // image1�̒l��null�ɂ��邪�A�t�@�C���̍폜���s��Ȃ�
                  'image1' => array('path'=>'/image/aaa/bbb', 'delete' => false)

                  // view�t�@�C��
                  $this->Form->input('image', array('type'=>'file'));
                  $this->Form->input('delete_image', array('type'=>'checkbox'));


��DB�ɕۑ�����鍀��
  �Ewebroot�ȉ��̃p�X���ۑ������̂�image�w���p�[��Alink�w���p�[�ɒ��ړn�����Ƃ��ł��܂��B
    ��j�摜��\��
      $this->Html->image($this->data['Hoge']['image']);
    ��j�����N��\��
      $this->Html->image('�Y�t�t�@�C��', $this->data['Hoge']['file']);
