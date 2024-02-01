<?php 
// VERIFICAÇÃO DE SESSÃO
if(!isset($_SESSION)){
    session_start();
    if(!isset($_SESSION['usuario'])){
        die('Você não está logado!' . '<a href="login.php">Clique aqui para logar</a>');
    }    
}
// FUNÇÃO VISUALIZAÇÕES DE CAMPO DATA E SENHA PADRÃO BR
function formatar_data($data){
    return implode('/', array_reverse(explode('-', $data)));
}

// FUNÇÃO FORMATAR TELEFONE PARA VISUALIZAÇÃO COM CARACTERES
function formatar_telefone($telefone){
    $ddd = substr ($telefone, 0, 2);
    $parte1 = substr ($telefone, 2, 5);
    $parte2 = substr ($telefone, 7);
        return "($ddd) $parte1-$parte2";
}
// FUNÇÃO LIMPAR CARACTERES 
function limpar_texto($str){ 
    return preg_replace("/[^0-9]/", "", $str); 
}
    
$id = intval($_GET['id']);
include('../conexao/conexao.php');
$error = "";
$sucess = "";

if(count($_POST) > 0){
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $nascimento = $_POST['nascimento'];
    $codigo = $_POST['id_exame'];

    if(empty($nome) || Strlen($nome) < 3 || Strlen($nome) > 100){
        $error = "Por favor, Prencha o campo nome corretamente. Capacidade mínima 3 dígitos! ";
    }

    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Por favor, Prencha o campo e-mail corretamente.";
    }  

    if(empty($nascimento) || strlen($nascimento) != 10){
        $error = "A data de nascimento deve ser preenchido no padrão dia/mes/ano*";
    }
    else{
        $pedacos = explode('/', $nascimento);
        if(count($pedacos) == 3){
        $nascimento = implode ('-', array_reverse($pedacos)); 
        } 
    }   

    if(empty($telefone)){
        $error = "campo telefone obrigatório*";}
        else{
            $telefone = limpar_texto($telefone);
            if(strlen($telefone) != 11){
                $error = "O telefone deve ser preenchido no padrão (11) 98888-8888";
        }   
    }
    // INSERÇÃO CAMPO EXAME NA TABELA PACIENTES_EXAMES
    if(!empty($codigo)){
        // VERIFICAÇÃO SE O EXAME EXISTE NA TABELA EXAMES
        $sql_verify = "SELECT * FROM exames WHERE exameid = '$codigo'";
        $query_verify = $mysqli->query($sql_verify);
        $verify_existencia_exame = $query_verify->fetch_assoc();

        if($verify_existencia_exame){
                $sql_verify = "SELECT * FROM pacientes_exames WHERE exame_id = '$codigo' AND paciente_id = '$id' ";
                $query_verify = $mysqli->query($sql_verify);
                $verify_cadastro_exame_no_paciente = $query_verify->fetch_assoc();
                        if($verify_cadastro_exame_no_paciente){
                            $error = "Exame já inserido*";
                            }
                            else{
                                $ql_insert = "INSERT INTO pacientes_exames (paciente_id, exame_id) VALUES ('$id', '$codigo')";
                                $query_insert = $mysqli->query($ql_insert);
                            }
            }else{
                $error = "Exame não existe";
            }
    }
    // VERIFICAÇÃO SE EXISTE ALGUM ERRO    
    if($error){
    }
    // ATUALIZAÇÃO DAS INFORMAÇÕES ALTERADAS
    else{
        $sql_code = "UPDATE pacientes
        SET nome = '$nome', 
        endereco = '$endereco',
        email      = '$email',
        telefone   = '$telefone',
        nascimento = '$nascimento' WHERE id  = '$id'";
        $deu_certo = $mysqli->query($sql_code);
            if($deu_certo){
                $sucess = "Atualizado com Sucesso!";
                unset($_POST);
            }
    }
    
}
// VISUALIZAÇÃO INFORMAÇÕES USUÁRIO NO CAMPO EDIÇÃO
include('../conexao/conexao.php');
$sql_cliente = "SELECT * FROM pacientes WHERE id = '$id'";
$query_cliente = $mysqli->query($sql_cliente) or die ($mysqli->error);
$cliente = $query_cliente->fetch_assoc();

// CAMPOS EXAMES DO PACIENTE / ID DO EXAME / ID DO PACIENTE
$sql_exame = "SELECT * FROM pacientes_exames AS pacex
    INNER JOIN exames ON exames.exameid = pacex.exame_id WHERE pacex.paciente_id = '$id'";

$query_exames = $mysqli->query($sql_exame);
$num_exames = $query_exames->num_rows;

 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do paciente</title>
</head>
<Style>
    body{
        width: 100%;
        height: 600px;
        display: flex;
    }
    .Info_pacientes{
        width: 50%;
        height: 800px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

</style>    

<body>
    <!-- INSERÇÃO CAMPOS POST NO FORM -->
    <div class="Info_pacientes">
        <div> 
            <a href="../index_pacientes/pacientes.php"><button class="button1">Retornar</button></a> 
            <a href="../SystemLocal/index.php">    <button class="button1">Pagina inicial</button></a>
        </div> 
        <div>   
            <h1>Informações do paciente:</h1>
            <form action="" method="POST">
                    <label>Nome: </label><br>
                    <input value="<?php echo $cliente['nome']; ?>" type="text" name="nome"><br><br>

                    <label>Endereço:</label><br>
                    <input value= "<?php echo $cliente['endereco']; ?>" type="text" name="endereco"><br><br>

                    <!-- <input value= "Masculino" type="radio" name="sexo">Masculino<br><br>
                    <input value= "Feminino" type="radio" name="sexo">Feminino<br><br> -->

                    <label>E-mail:</label><br>
                    <input value ="<?php if(!empty($cliente['telefone'])){ echo ($cliente['email']);} ?>" type="email" name="email"><br><br>

                    <label>Telefone:</label><br>
                    <input value ="<?php if(!empty($cliente['telefone'])){ echo formatar_telefone($cliente['telefone']);} ?>" placeholder="(11) 98888-8888" type="text" name="telefone"><br><br>

                    <label>Data de nascimento:</label><br>
                    <input value ="<?php if(!empty($cliente['nascimento'])){ echo formatar_data($cliente['nascimento']);} ?>" placeholder="dia/mês/ano" type="text" name="nascimento"><br>
                <p>
                    <p>Adicionar um exame no atendimento:</p>
                    <label>Exame ID</label>
                    <input  type="text" name="id_exame"><br><br>
                    <button type="submit">Enviar</button>
                </p>
            </form>
            <?php
                    if(isset($error)) echo $error;
                    if(isset($sucess)) echo $sucess;
            ?>
        </div>
        
    <!-- TABELA DE INFORMAÇÕES EXAMES CADASTRADOS DO PACIENTE -->
        <table cellpadding="10">
            <thead>
                <th>ID Exames</th>
                <th>código exame</th>
                <th>Nome exame</th>
                <th>Resultado</th>
                <th>Inserir Resultado</th>
                <th>Remover</th>

            </thead>
            <tbody> <?php if($num_exames == 0) {?>
                <tr>
                    <td colspan="7">Nenhum exame foi encontrado!</td>
                </tr> <?php } ?>
            <?php while($exames = $query_exames->fetch_assoc()){?>
                    
                <tr>
                    <td><?php echo $exames['exame_id']?></td>
                    <td><?php echo $exames['codigo']?></td>
                    <td><?php echo $exames['descricao']?></td>
                    
                    <td><?php if($exames['resultado'] == 0){ }else echo number_format($exames['resultado'], 1, ',', '.')?></td>
                    <td><a href="inserir_resultado.php?id=<?php echo $exames['id']?>">inserir</a></td>
                    <td><a href="remover_exame.php?id=<?php echo $exames['id']?>">X</a></td>
                </tr><?php }?> 
            </tbody>
        </table>
</div>
</body>
</html>
