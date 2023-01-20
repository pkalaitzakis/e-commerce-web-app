const registration = new JustValidate("#signup_form");

registration
    .addField("#name", [
        {
            rule: "required"
        }

    ])
    .addField("#surname",[
        {
            rule: "required"
        }
    ])
    .addField("#email",[
        {
            rule: "required"
        },
        {
            rule: "email"
        }
    ])
    .addField("#username",[
        {
            rule: "required"
        }
    ])
    .addField("#password",[
        {
            rule: "required"
        },
        {
            rule:"password"
        }
    ])
    .addField("#password_confirmation",[
        {
            rule: "required"
        },
        {
            validator: (value, fields) => {
                return value===fields['#password'].elem.value;
            },
            errorMessage: "Passwords should match"
        }
    ])
    .onSuccess(()=> {
        document.getElementById("signup_form").submit();
    })
