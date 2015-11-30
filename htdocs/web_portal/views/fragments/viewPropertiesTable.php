
<!--  Custom Properties -->
<div class="tableContainer" style="width: 99.5%; float: left; margin-top: 3em; margin-right: 10px;">
    <span class="header" style="vertical-align:middle; float: left; padding-top: 0.9em; padding-left: 1em;">Extension Properties</span>
    <img src="<?php echo \GocContextPath::getPath()?>img/keypair.png" height="25px" style="float: right; padding-right: 1em; padding-top: 0.5em; padding-bottom: 0.5em;" />
    <table id="extensionPropsTable" class="table table-striped table-condensed tablesorter">
        <thead>
        <tr>
            <th>Name</th>
            <th>Value</th>
            <?php if(!$params['portalIsReadOnly']): ?>
                <th>Edit</th>
                <th><input type="checkbox" id="selectAllProps"/> Select All</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        //$num = 2;
        foreach($extensionProperties as $prop) {
            ?>

            <tr>
                <td style="width: 35%;"><?php xecho($prop->getKeyName()); ?></td>
                <td style="width: 35%;"><?php xecho($prop->getKeyValue()); ?></td>
                <?php if(!$params['portalIsReadOnly']): ?>
                    <td style="width: 10%;"><a href="index.php?Page_Type=Edit_Service_Group_Property&propertyid=<?php echo $prop->getId();?>&id=<?php echo $parent->getId();?>"><img height="25px" src="<?php echo \GocContextPath::getPath()?>img/pencil.png"/></a></td>
                    <!--	                <td style="width: 10%;"><a href="index.php?Page_Type=Delete_Service_Group_Property&propertyid=--><?php //echo $sp->getId();?><!--&id=--><?php //echo $params['sGroup']->getId();?><!--"><img height="25px" src="--><?php //echo \GocContextPath::getPath()?><!--img/cross.png"/></a></td>-->
                    <td style="width: 10%;"><input type='checkbox' class="propCheckBox" form="Modify_Properties_Form" name='selectedPropIDs[]' value="<?php echo $prop->getId();?>" autocomplete="off"/></td>

                <?php endif; ?>
            </tr>
            <?php
            //if($num == 1) { $num = 2; } else { $num = 1; }
        }
        ?>
        </tbody>
    </table>
    <!--  only show this link if we're in read / write mode -->
    <?php if(!$params['portalIsReadOnly'] && $params['ShowEdit']): ?>
        <!-- Add new data Link -->
        <a href="index.php?Page_Type=<?php echo $addPropertiesPage?>&parentid=<?php echo $parent->getId()?>">
            <img src="<?php echo \GocContextPath::getPath()?>img/add.png" height="50px" style="float: left; padding-top: 0.9em; padding-left: 1.2em; padding-bottom: 0.9em;"/>
                <span class="header" style="vertical-align:middle; float: left; padding-top: 1.1em; padding-left: 1em; padding-bottom: 0.9em;">
                        Add Properties
                </span>
        </a>
        <form action="index.php?Page_Type=<?php echo $propertiesController;?>" method="post" id="Modify_Properties_Form" style="vertical-align:middle; float: right; padding-top: 1.1em; padding-right: 1em; padding-bottom: 0.9em;">
            <input class="input_input_text" type="hidden" name ="parentID" value="<?php echo $parent->getId();?>" />
            <input class="input_input_hidden" type="hidden" name="UserConfirmed" value="true" />
            <select id="propActionSelect" name="action" autocomplete="off">
                <option value="" disabled selected>Select action...</option>
                <option value="delete">Delete</option>
            </select>

            <input class="input_button" type="button" onclick="return confirmPropAction()" value="Modify Selected Properties" />
        </form>
        <script>
            //This checks that the user has selected at least one property and an action
            //and then asks for conformation, and submits the form.
            function confirmPropAction() {
                //number of checked properties
                var numPropsSelected = $('#extensionPropsTable').find('input[type=checkbox]:checked').length;
                //name of action
                var propAction = $("#propActionSelect").val();
                if (propAction != null && numPropsSelected != 0){
                    //confirmation box
                    if (confirm("Do you wish to perform the action \"" + propAction + "\" on " + numPropsSelected + " property(s).") == true){
                        $("#Modify_Properties_Form").submit();
                    }
                } else {
                    alert("Please select at least one property, and an action to perform.")
                }
            }

            $(document).ready(function () {
                //register handler for the select/deselect all properties checkbox
                $("#selectAllProps").change(function(){
                    $(".propCheckBox").prop('checked', $(this).prop("checked"));
                });
            });

        </script>

    <?php endif; ?>
</div>
