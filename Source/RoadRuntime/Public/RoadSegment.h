#pragma once

#include "CoreMinimal.h"
#include "GameFramework/Actor.h"
#include "RoadSegment.generated.h"

class ARoadNode;
class USplineComponent;

UCLASS()
class ARoadSegment : public AActor
{
    GENERATED_BODY()

public:
    ARoadSegment();

    void InitializeSegment(ARoadNode* InStart, ARoadNode* InEnd);
    ARoadNode* GetStartNode() const;
    ARoadNode* GetEndNode() const;
    USplineComponent* GetSplineComponent() const;

protected:
    virtual void EndPlay(const EEndPlayReason::Type EndPlayReason) override;

private:
    void RefreshSpline();

    UPROPERTY(VisibleAnywhere, Category="Road", meta=(AllowPrivateAccess="true"))
    USplineComponent* SplineComponent;

    UPROPERTY(VisibleAnywhere, Category="Road", meta=(AllowPrivateAccess="true"))
    ARoadNode* StartNode;

    UPROPERTY(VisibleAnywhere, Category="Road", meta=(AllowPrivateAccess="true"))
    ARoadNode* EndNode;
};
