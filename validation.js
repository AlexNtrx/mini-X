const validation = new JustValidate('#form-signup');

validation
.addField('#username-signup',[
    {
        rule: "required"
    }
]);